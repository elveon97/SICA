<?php
    date_default_timezone_set("America/Mexico_City");

    include("conectar.php");
    include("class.phpmailer.php");
    include("class.smtp.php");    

    $hora = date("H:i:s");    
    echo $hora . "<br>";
    $horaIni = 'Invalido'; 
    $horaFin = 'Invalido';
    if ($hora == '12:00:00') {
        $horaIni = '07:00:00';
        $horaFin = '11:00:00';
    } else if ($hora == '17:00:00') {
        $horaIni = '11:00:00';
        $horaFin = '16:00:00';
    } else if ($hora == '21:40:00') {
        $horaIni = '16:00:00';
        $horaFin = '21:30:00';
    }

    echo $horaIni . "<br>";
    echo $horaFin . "<br>";

    $query = "CALL get_faltas_clases_rango('$horaIni', '$horaFin')";
    set_time_limit(150);

    if ($result = mysqli_query($con, $query)) {
        $today = date("d/m/Y");

        while($row = mysqli_fetch_assoc($result)) {

            echo $row['nombre'].": ";
            echo $hora."<br>";
            //echo var_dump($row);

            if (filter_var($row["correo"], FILTER_VALIDATE_EMAIL) && $row['crn'] != 147450 && $row['crn'] != 145069 ) {
                //enviar email

                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->SMTPAuth = false;
                //$mail->SMTPSecure = "ssl";

                $mail->Host = MAIL_HOST;
                $mail->Port = MAIL_PORT;
                $mail->Username = MAIL_USERNAME;
                $mail->Password = MAIL_PASSWORD;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'tls';

                $mail->SetFrom (MAIL_USERNAME,"Sistema de Control de Asistencia(SiCA) CUSUR");

                $mail->Subject = "CUSUR SICA - Falta a asignatura";
                $mail->AltBody = "Correo de incidencia";

                $mensaje = "<div align='justified'>	<p><b>Acad&eacute;mico $row[nombre] </b></p>
                    <p align='justify'>Se ha generado una falta en el sistema el d&iacute;a <b>$row[dia] $today</b>, en la materia <b>$row[materia] ($row[crn])</b> registrada en el horario <b>$row[horario] hrs.</b> por incidencia en registro de <b>entrada o salida</b>. Deber&aacute; acudir con su Jefe de Departamento a efectuar la justificaci&oacute;n correspondiente; se le recuerda que tiene 5 d&iacute;as h&aacute;biles para presentarla, de lo contrario se proceder&aacute; conforme a la normatividad.</p>
                    <p align='justify' style='color:grey;'>Este es un correo autom&aacute;tico generado por el Sistema de Control de Asistencia (SICA) del Centro Universitario del Sur de la Universidad de Guadalajara. No es necesario que responda a este correo.</p>
                    <p>Sistema de Control de Asistencia, CUSUR.</p></div>";

                $mail->MsgHTML($mensaje);
                $mail->IsHTML(true);
                $mail->AddAddress($row["correo"],$row["nombre"]);
                //$mail->AddCC("buzonsica@valles.udg.mx","SiCA");                

                $flag = false;

                for ($i=1 ; $i<4 && !$flag ; $i++){
                    set_time_limit(60);
                    $flag = $mail->Send();
                    if(!$flag) {
                        echo "Error: " . $mail->ErrorInfo."</br>";
                    } else {
                       echo $row['nombre'];
                        echo "Mensaje enviado correctamente</br>";
                    }
                }


            } else {
                echo "Error: usuario no cuenta con correo electronico o este es invalido</br>";
            }
        }
        mysqli_free_result($result);

    } else {
        echo mysqli_error($con);
    }
?>