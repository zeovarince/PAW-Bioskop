<?php
session_start();
include "koneksi.php";
function checklogin ($data){
    global $conn;
    $email = mysqli_real_escape_string($conn,$data['email']);
    $password = $data['password'];

    $query = "SELECT * FROM users WHERE email ='$email' AND password= '$password' ";
    $result = mysqli_query($conn,$query);

    if (mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        $_SESSION['login'] = true;
        $_SESSION['user_id'] = $user['Id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = ($user['role']);

        if ($user ['role'] == '1'){
            header("location: admin/index.php");
        }else{
            header("location: custommer/index.php");
        }
        exit;

    }else { 
        return "email dan password anda salah";
    }
}

function validateEmail(&$errors, $field_list, $field_name){
    $email = strtolower(trim($field_list[$field_name] ?? ''));
    if (empty($email)) {
        $errors[$field_name] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[$field_name] = 'Format email tidak valid.';
    }
}
function validatePassword(&$errors, $field_list, $field_name){
    $password = $field_list[$field_name] ?? '';
    if (empty($password)) {
        $errors[$field_name] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors[$field_name] = 'Password minimal 6 karakter.';
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors[$field_name] = 'Password harus ada huruf besar.';
    }
}
function validateName(&$errors, $field_list, $field_name)
{
    $pattern = "/^[a-zA-Z' -]+$/";
    if (empty(trim($field_list[$field_name] ?? ''))) {
        $errors[$field_name] = 'Nama wajib diisi.';
    } elseif (!preg_match($pattern, $field_list[$field_name])) {
        $errors[$field_name] = 'Nama hanya boleh huruf dan spasi.';
    }
}
?>