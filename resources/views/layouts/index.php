<?php

// Function to calculate the modular inverse of a number
function modInverse($a, $m) {
    for ($x = 1; $x < $m; $x++) {
        if (($a * $x) % $m == 1) {
            return $x;
        }
    }
    return null;
}

// Function to calculate the determinant of a matrix modulo m
function modDet($matrix, $m) {
    $det = 0;
    $n = count($matrix);
    if ($n == 2) {
        // 2x2 matrix determinant
        $det = ($matrix[0][0] * $matrix[1][1] - $matrix[0][1] * $matrix[1][0]) % $m;
    }
    return $det < 0 ? $det + $m : $det;
}

// Function to create the key matrix from the key string
function createKeyMatrix($key, $size) {
    $matrix = [];
    $key = strtoupper($key);
    for ($i = 0; $i < $size; $i++) {
        for ($j = 0; $j < $size; $j++) {
            $matrix[$i][$j] = ord($key[$i * $size + $j]) - 65;
        }
    }
    return $matrix;
}

// Function to perform matrix multiplication modulo m
function matrixMultiply($matrix1, $matrix2, $m) {
    $size = count($matrix1);
    $result = array_fill(0, $size, array_fill(0, $size, 0));
    for ($i = 0; $i < $size; $i++) {
        for ($j = 0; $j < $size; $j++) {
            for ($k = 0; $k < $size; $k++) {
                $result[$i][$j] = ($result[$i][$j] + $matrix1[$i][$k] * $matrix2[$k][$j]) % $m;
            }
        }
    }
    return $result;
}

// Function to encrypt the plaintext using the Hill cipher
function hillEncrypt($plainText, $key, $size = 2) {
    // Remove spaces and convert to uppercase
    $plainText = strtoupper(str_replace(" ", "", $plainText));
    
    // If the plaintext length is not a multiple of the size, pad with X
    $length = strlen($plainText);
    if ($length % $size != 0) {
        $plainText .= str_repeat("X", $size - $length % $size);
    }

    // Create the key matrix
    $keyMatrix = createKeyMatrix($key, $size);

    // Convert the plaintext to numerical values (A=0, B=1, ..., Z=25)
    $plainNumbers = [];
    for ($i = 0; $i < $length; $i++) {
        $plainNumbers[] = ord($plainText[$i]) - 65;
    }

    // Encrypt the plaintext in blocks
    $cipherText = '';
    for ($i = 0; $i < $length; $i += $size) {
        $block = array_slice($plainNumbers, $i, $size);
        $encryptedBlock = matrixMultiply([$block], $keyMatrix, 26)[0];
        foreach ($encryptedBlock as $num) {
            $cipherText .= chr($num + 65);
        }
    }

    return $cipherText;
}

// Function to find the matrix inverse modulo m
function matrixInverse($matrix, $m) {
    $det = modDet($matrix, $m);
    $invDet = modInverse($det, $m);
    if ($invDet === null) {
        throw new Exception("Matrix is not invertible");
    }
    $size = count($matrix);
    $adjoint = [];
    for ($i = 0; $i < $size; $i++) {
        for ($j = 0; $j < $size; $j++) {
            $minor = getMinor($matrix, $i, $j);
            $adjoint[$j][$i] = modDet($minor, $m) * ($i + $j) % 2 == 0 ? 1 : -1;
            $adjoint[$j][$i] = ($adjoint[$j][$i] * $invDet) % $m;
        }
    }
    return $adjoint;
}

// Helper function to calculate the minor of a matrix
function getMinor($matrix, $row, $col) {
    $minor = [];
    foreach ($matrix as $i => $rowData) {
        if ($i == $row) continue;
        $minorRow = [];
        foreach ($rowData as $j => $value) {
            if ($j == $col) continue;
            $minorRow[] = $value;
        }
        $minor[] = $minorRow;
    }
    return $minor;
}

// Function to decrypt the ciphertext using the Hill cipher
function hillDecrypt($cipherText, $key, $size = 2) {
    // Create the key matrix
    $keyMatrix = createKeyMatrix($key, $size);

    // Find the inverse of the key matrix modulo 26
    $keyMatrixInverse = matrixInverse($keyMatrix, 26);

    // Convert the ciphertext to numerical values (A=0, B=1, ..., Z=25)
    $cipherNumbers = [];
    $length = strlen($cipherText);
    for ($i = 0; $i < $length; $i++) {
        $cipherNumbers[] = ord($cipherText[$i]) - 65;
    }

    // Decrypt the ciphertext in blocks
    $plainText = '';
    for ($i = 0; $i < $length; $i += $size) {
        $block = array_slice($cipherNumbers, $i, $size);
        $decryptedBlock = matrixMultiply([$block], $keyMatrixInverse, 26)[0];
        foreach ($decryptedBlock as $num) {
            $plainText .= chr($num + 65);
        }
    }

    return $plainText;
}

// Example usage
$key = "GYBNQKURP";  // 3x3 key
$plainText = "HELLO";

$cipherText = hillEncrypt($plainText, $key, 3);
echo "Cipher Text: " . $cipherText . "\n";

$decryptedText = hillDecrypt($cipherText, $key, 3);
echo "Decrypted Text: " . $decryptedText . "\n";

?>
