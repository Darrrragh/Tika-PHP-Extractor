

<html>
<head>
<title>Upload Form</title>
</head>
<body>
 
 
<h2>Choose a file:</h2>
 
<br /><br />
 
<input type="submit" type="file" value="upload" name="upload" method="post" action=""/>

			<label for="file">Filename:</label>
			<input type="file" name="file" id="file">
			
		</form>

		<?php 

		 if($_SERVER['REQUEST_METHOD'] === 'POST') {
		  if ($_FILES["file"]["error"] > 0) {
		    echo "Error: " . $_FILES["file"]["error"] . "<br>";
		  }
		  else {

		    echo "File: " . $_FILES["file"]["name"] . "<br>";
		    echo "Type: " . $_FILES["file"]["type"] . "<br>";
		    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
		    echo "Stored in: " . $_FILES["file"]["tmp_name"];
			echo '<br/> <br/>';
			//$contents = shell_exec('java -jar tika-app-1.4.jar -j '.$_FILES["file"]["tmp_name"]);
			$contentsAbs = shell_exec('java -jar tika-app-1.4.jar -x '.$_FILES["file"]["tmp_name"]);


		// extracting the metadata from here : 
/*
			$data = json_decode($contents);

			$this->firephp->log('data');
			$this->firephp->log($data);

			//echo getComponent("Author", $data->creator);

			if(empty($data->title)){
				echo '<b>'."Title: ".'</b>'."Not found";
			echo "<br />";
}
			else{
				echo getComponent("Title", $data->title);
			}
			//echo getComponent("Date", $data->created);
			
			if(empty($data->producer)){
				echo '<b>'."Producer: ".'</b>'."Not found";
			echo "<br />";
		}
			else{
				echo getComponent("Producer", $data->producer);
			}
	

			// Extraction of Abstract
			$String = $contentsAbs;

			$First = "Abstract";
			$Second = "Introduction";

			$Firstpos=strpos($String, $First);
			$Secondpos=strpos($String, $Second);

			$id = substr($String , $Firstpos+strlen($First), $Secondpos-strlen($Second));

			echo "<br />";
			echo "<br />";

			echo '<b>'."Abstract : ".'</b>'. $id;

*/
	
    	 $string_data = $contentsAbs;
		 $xml = simplexml_load_string($string_data);

		// echo '<pre>'; print_r($xml); echo '</pre>';


	
	

//Extract email address: 

	echo '<b>'."Email address : ".'</b>';
	$emailstring ="";
	$mail="";
	
	for ($x = 3; $x <= 7; $x++) {
    	
		if (stripos($xml->body->div->p[$x], "@") !== false) {
	   			
	   			$email=$xml->body->div->p[$x];
	   			// echo $email;
	   			// $emailstring .= $email."|";
			$regex = "^[a-zA-Z0-9_. +-\{\}| , ]+@[a-zA-Z0-9-, ]+\.[a-zA-Z0-9-., ]+$^";
			
			$matches=array();
			preg_match_all($regex, $email, $matches);

			// echo $email . '<br />';

			foreach ($matches[0] as $match) {
			   
			    $arr = preg_split('/[,|\\|,]/', $match);	
	   		   	if(count($arr)===1){
	   		   	
	   		   		echo $arr[0].", ";
	   		   		$emailstring .= $arr[0];
	   		   	}else{
	   		   	
		   		   		$mail =  $arr[count($arr)-1];
		   		   		$mail = substr($mail , stripos($mail,'}')+1, strlen($mail));
		
		   		   		foreach (new RecursiveArrayIterator($arr) as $a) {
					   
						    if (strpos($a,'{') !== false) {

							$a = substr($a , stripos($a,'{')+1, strlen($a));    
							
							$e=$a.$mail.", ";
							echo $e;	
							$emailstring .= $e;

							}elseif (strpos($a,'}') !== false) {

								$a = substr($a , 0, strpos($a,'}'));
							 	
							 	$e=$a.$mail.", ";
								echo $e;	
								$emailstring .= $e;
							
							}else{
								
								$e=$a.$mail.", ";
								echo $e;	
								$emailstring .= $e;
							}
						
						}


	   		   		}
			    
			    
			}

			
			//echo '<pre>'; print_r($matches); echo '</pre>';
	   		//$emailstring .= $email."|";
		}
	}
// Extract title 

	echo "<br />";
	echo "<br />";

	$title=(string)$xml->body->div->p[1];
			
	echo '<b>'."Title : ".'</b>'.$title;
			
// Extract Abstract

	 echo "<br />";
	 echo "<br />";
	  echo '<b>'."Abstract : ".'</b>';
	 $abstractString = "";
	for ($y = 4; $y <= 9; $y++){

		if (stripos($xml->body->div->p[$y], "Abstract") !== false) {

			if(str_word_count($xml->body->div->p[$y])<2){

				$abs=$xml->body->div->p[$y+1];
				echo $abs;
				$abstractString .= $abs;
			}

			else{

	   			echo $abs =$xml->body->div->p[$y];
	   			$String= $xml->body->div->p[$y];
	   			$abs = substr($String , '9', strlen($String));
	   			echo $abs;
				$abstractString .= $abs;
			}
	   		
		}
	}
	

// Produces: INSERT INTO mytable (title, name, date) VALUES ('My title', 'My name', 'My date')


	// Storing into the database- CI, Table- extraction

	//$sql = "INSERT INTO extraction (Email, title, abstract) VALUES ('$emailstring', '$title', '$abstractString')";
	//$this->db->query($sql);

		  }



		 $paperinfo = array(
  'Email' => $emailstring,
   'title' => $title,
   'abstract' => $abstractString
);

//var_dump($paperinfo);

$this->db->insert('extraction', $paperinfo); 
		 }	


$query = $this->db->get('extraction');

foreach ($query->result() as $row)
{
    echo $row->title;
}

		?>	
 
</form>	
</body>
</html>

