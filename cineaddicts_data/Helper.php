<?php

class Helper {

    public static function dataLogger($fileName, $msg, $subDirectory = null){
            
        $logDirectory = 'logs/'.date('Y/m_M/d_D'); // Log files location (E.g. 'logs/YYYY/MM_Month/DD_Day')
        
        //	If there's a subdirectory
        if(!is_null($subDirectory)){
            $logDirectory .= '/'. strtolower($subDirectory);
        }

        //	If the directory doesn't exists, create it
        if (!file_exists($logDirectory)) {

            //	If the given directory could not be created
            if(!mkdir($logDirectory, 0777, true)){
                throw new Exception("ERROR: Unable to create the following directory: '{$logDirectory}'.");
            }
        }
        
        //	Write the log data into the given log file
        $filePutResult = file_put_contents($logDirectory .'/'. $fileName, date('H:i:s').' | '.$msg.PHP_EOL, FILE_APPEND);

        //	If the data couldn't be written
        if($filePutResult === false){
            throw new Exception("ERROR: Unable to write data the following directory: '{$logDirectory}'.");
        }
    }

    public static function parseCSV($csvFile, $delimiter = ','){

        //	Reads entire file into an array. Each element of the array corresponds to a line in the file.
        $fileLines = file($csvFile);
        
        //	If the file was successfully read
        if($fileLines !== false){
            $rowFields  = [];
            foreach($fileLines as $fileLine) {
                //	Parse the CSV strings into an array
                $rowFields[] = str_getcsv($fileLine, $delimiter);
            }
            //	Shift an element off the beginning of array
            $csvHeader = array_shift($rowFields);
            
            $parsedCSV = [];
            foreach($rowFields as $csvData) {

                $parsedCSV[] = array_combine($csvHeader, $csvData);
            }
            return $parsedCSV;
        }
        else{
            $lastError = error_get_last();
            throw new Exception("ERROR: Failed to read the '{$csvFile}' CSV file ->\n{$lastError['message']}.");
        }
    }

    public static function csvLogger($fileName, $headers, $data, $delimiter = ',', $subDirectory = null){
		$logDirectory = 'logs/'.date('Y/m_M/d_D'); // Log files location (E.g. 'logs/YYYY/MM_Month/DD_Day')
		
		//	If there's a subdirectory
		if(!is_null($subDirectory)){
			$logDirectory .= '/'. strtolower($subDirectory);
		}

		//	If the directory doesn't exists, create it
		if (!file_exists($logDirectory)) {

			//	If the given directory could not be created
			if(!mkdir($logDirectory, 0777, true)){
				throw new Exception("ERROR: Unable to create the following directory: '{$logDirectory}'.");
			}
		}
		
		//	Create a file pointer connected to the output stream
		$fullPath = $logDirectory .'/'. $fileName;
		$output = fopen($fullPath, 'w');

		//	If no error occurred
		if($output !== false){
			//	Format the lines (passed as array) as CSV and write them to the given file
			if(fputcsv($output, $headers, $delimiter) !== false){
				foreach ($data as $dataLine) {

					if(fputcsv($output, $dataLine, $delimiter) === false){
						$valuesString = implode(', ', $dataLine);
						throw new Exception("ERROR: Unable to write the following values '{$valuesString}' in to the following file '{$fullPath}'.");
					}
				}
			}
			else{
				$headersString = implode(', ', $headers);
				throw new Exception("ERROR: Unable to write the following headers '{$headersString}' in to the following file '{$fullPath}'.");
			}
		}
		else{
			throw new Exception("ERROR: Unable to open the following file: '{$fullPath}'.");
		}		
	}


    public static function makeRequest($id, $requestCode){
		
		$apiResult = ['success' => false, 'request' => null, 'http_code' => null,  'response' => null];
		
        //	Build the full URL for the given request
        if($requestCode == 0){
                $requestURL = "https://www.wikidata.org/w/api.php?action=wbgetentities&ids={$id}&format=json&languages=en"; 
        }
        else if ($requestCode == 1){
            $requestURL = "http://api.themoviedb.org/3/find/{$id}?api_key=10462fa47fb42da4094335ab2922cb91&external_source=imdb_id&format=json"; 
        }
        else if($requestCode == 2){
            $requestURL = "https://api.themoviedb.org/3/person/{$id}?api_key=10462fa47fb42da4094335ab2922cb91";
        }

        $apiResult['request'] = ['method' => 'GET', 'url' => $requestURL];
        
        //	Initializing a new cURL session and performing the specified request
        $ch = curl_init();
        
        //	Set various options on the given cURL session handle
        curl_setopt($ch, CURLOPT_URL, $requestURL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-CineAddicts/1.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        //	Perform the given cURL session
        $curlResponse = curl_exec($ch);

        //	Get and set the last received HTTP code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $apiResult['http_code'] = $httpCode;

        $decodedResponse = null;
        //	If the request was successful
        if($curlResponse !== false){
            //	Try to convert the result (JSON encoded string) into an associative array
            $decodedResponse = json_decode($curlResponse, true);

            //	If the result cannot be decoded
            if(is_null($decodedResponse)){
                //	It's either because the encoded data was badly formatted or was deeper than the recursion limit
                $apiResult['error'] = ['type' => 'JSON', 'code' => json_last_error(), 'message' => 'Unexpected response format: '.json_last_error_msg()];
            }
        }
        else{
            //	It failed because of a cURL error
            $apiResult['error'] = ['type' => 'cURL', 'code' => curl_errno($ch), 'message' => curl_error($ch)];
        }

        //	Closes the given cURL session and frees all resources
        curl_close($ch);
        
        //	If some data has been received and has been decoded successfully
        if(!is_null($decodedResponse)){
            //	If the requested was received, understood and accepted (if status/http code is equals to 2XX)
            if ($httpCode >= 200 && $httpCode < 300) {
                $apiResult['success'] = true;
                $apiResult['response'] = $decodedResponse;
            }
            else{
                $msg = array_key_exists($httpCode, $this->statusCodesResponses) ? $this->statusCodesResponses[$httpCode] : 'Unknown';
                $apiResult['error'] = ['type' => 'WIKI', 'code' => $httpCode, 'message' => $msg, 'details' => $decodedResponse['errors']];
            }
        }
		
		return $apiResult;
    }
}

?>