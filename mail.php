<?php
if(mail("alexs199.ale@gmail.com", "Test mail", "Mensaje de prueba", "From: alexs199.ale@gmail.com\r\n")) {
    echo "¡Correo enviado!";
} else {
    echo "No se pudo enviar el correo.";
}
?>