<!DOCTYPE html>
<html>
	<head>
		<title>Результат</title>
		<meta charset="utf-8"/>
	</head>
</html>

<?php
	include "./database.php";
	
	$inn = $_POST["inn"];
	echo "Введённый ИНН: <b>".$inn . "</b> <br>";
	
	//Проверка на правильность ИНН, я проверяю только 12-ти значный, тк самозанятым может быть только физ. лицо,
	//а у физ лиц 12-ти значный ИНН.
	if (inn_validation($inn) == $inn)
	{
		echo "ИНН корректен<br>";
		//проверить наличие информации об ИНН в БД
		$db_message = get_inn($inn);
		if ($db_message !== false)
		{
			echo $db_message;
		}
		else
		{
			$result = check_inn($inn);
			//если в ответе пришло заполненное поле code, значит пришла ошибка
			if (isset($result["code"]))
			{
				echo "Ошибка. Код: " . $result["code"] . " " . $result["message"];
			}
			else
			{
				//результат проверки
				echo $result["message"];
				//добавляю результат в БД
				add_inn($inn, $result["message"], date("Y-m-d"));
			}
		}
	}
	else
	{
		echo "ИНН некорректен<br>";
	}
	
	function inn_validation($_inn)
	{
		//preg_match я нагуглил, как я понимаю, тут я проверяю состоит ли строка $_inn из 12 цифр
		if (preg_match("#([\d]{12})#", $_inn, $m))
		{
			$_inn = $m[0];
			
			$checkDigit1 = (($_inn[0] * 7 + $_inn[1] * 2 + $_inn[2] * 4 + 
				$_inn[3] * 10 + $_inn[4] * 3 + $_inn[5] * 5 + 
				$_inn[6] * 9 + $_inn[7] * 4 + $_inn[8] * 6 + 
				$_inn[9] * 8 + $_inn[10] * 0) % 11) % 10;
			$checkDigit2 = (($_inn[0] * 3 + $_inn[1] * 7 + $_inn[2] * 2 + 
				$_inn[3] * 4 + $_inn[4] * 10 + $_inn[5] * 3 + 
				$_inn[6] * 5 + $_inn[7] * 9 + $_inn[8] * 4 + 
				$_inn[9] * 6 + $_inn[10] * 8 + $_inn[11] * 0) % 11) % 10;
			
			if ($checkDigit1 == $_inn[10] && $checkDigit2 == $_inn[11])
				return $_inn;
		}
		return false;
	}
	
	function check_inn ($_inn)
	{
		try
		{
			//формирую запрос в нужном формате
			$data = array
			(
				"inn" => $_inn,
				"requestDate" => date("Y-m-d")
			);
			$data = json_encode($data);
			
			$url = "https://statusnpd.nalog.ru/api/v1/tracker/taxpayer_status";
			
			$options = array
			(
				"http" => array
				(
					"method" => "POST",
					"header" => array("Content-type: application/json",),
					"timeout" => 60,
					"content" => $data
				)
			);
			$context  = stream_context_create($options);
			//связываюсь с налоговой
			$response = file_get_contents($url, false, $context);
			
			/*
			//это я нагуглил, но curl не захотел сотрудничать со мной
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$url");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
			$response = curl_exec($ch);
			curl_close($ch);
			*/
			
			//использовал для проверки коннекта с апи налоговой
			if ($response === FALSE)
			{
				echo "чёт не так";
				return false;
			}
			
			$response = json_decode($response, true);
			return $response;
		}
		catch (Throwable $ex)
		{
			echo $ex["message"];
		}
	}
?>