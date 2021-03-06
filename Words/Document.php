<?php

/*
* Deals with Word document level aspects
*/
class WordDocument
{
        public $FileName = "";
		
		
		public function WordDocument($fileName)
        {
            $this->FileName = $fileName;
        }

		
	/*
    * Appends a list of documents to this one.
	* @param string $appendDocs (List of documents to append)
	* @param string $importFormatModes
	* @param string $sourceFolder (name of the folder where documents are present)
	*/
	public function AppendDocument($appendDocs, $importFormatModes, $sourceFolder) {
       try {
			//check whether files are set or not
			if ($this->FileName == "")
				throw new Exception("Base file not specified");
			//check whether required information is complete
			if (count($appendDocs) != count($importFormatModes))
				throw new Exception("Please specify complete documents and import format modes");
			
			//Build JSON to post
			$json = '{ "DocumentEntries": [';
 
			for ($i = 0; $i < count($appendDocs); $i++) {
				$json .= '{ "Href": "' . $sourceFolder . $appendDocs[$i] . 
					'", "ImportFormatMode": "' . $importFormatModes[$i] . '" }' . 
					(($i < (count($appendDocs) - 1)) ? ',' : '');
			}
			
            $json .= '  ] }';

			//build URI to merge Docs
			$strURI = Product::$BaseProductUri . "/words/" . $this->FileName . "/appendDocument";
			
			//sign URI
			$signedURI = Utils::Sign($strURI);

			$responseStream = Utils::processCommand($signedURI, "POST", "json", $json);

			$v_output = Utils::ValidateOutput($responseStream);
 
			if ($v_output === "") {
				//Save merged docs on server
				$folder = new Folder();
				$outputStream = $folder->GetFile($sourceFolder . (($sourceFolder == '') ? '' : '/') . $this->FileName);
				$outputPath = SaasposeApp::$OutPutLocation . $this->FileName;
				Utils::saveFile($outputStream, $outputPath);
				return "";
			} 
			else 
				return $v_output;
		}
		catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
    }
	
	/*
    * Get Resource Properties information like document source format, IsEncrypted, IsSigned and document properties
	*/		
	public function GetDocumentInfo(){
		try{
			//check whether files are set or not
			if ($this->FileName == "")
				throw new Exception("Base file not specified");
			
			//build URI to merge Docs
			$strURI = Product::$BaseProductUri . "/words/" . $this->FileName;
			
			//sign URI
			$signedURI = Utils::Sign($strURI);
	
			$responseStream = Utils::processCommand($signedURI, "GET", "", "");
			
			$json = json_decode($responseStream);
			
			if($json->Code == 200)
				return $json->Document;
			else
				return false;			
					
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	
	/*
    * Get Resource Properties information like document source format, IsEncrypted, IsSigned and document properties
	@param string $propertyName
	*/		
	public function GetProperty($propertyName){
		try{
			//check whether files are set or not
			if ($this->FileName == "")
				throw new Exception("Base file not specified");
			
			if ($propertyName == "")
				throw new Exception("Property Name not specified");
			
			//build URI to merge Docs
			$strURI = Product::$BaseProductUri . "/words/" . $this->FileName . "/documentProperties/" . $propertyName;
			
			//sign URI
			$signedURI = Utils::Sign($strURI);
	
			$responseStream = Utils::processCommand($signedURI, "GET", "", "");
			
			$json = json_decode($responseStream);
						
			
			if($json->Code == 200)
				return $json->DocumentProperty;
			else
				return false;			
					
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	
	/*
    * Set document property
	@param string $propertyName
	@param string $propertyValue
	*/		
	public function SetProperty($propertyName="",$propertyValue=""){
		try{
			//check whether files are set or not
			if ($this->FileName == "")
				throw new Exception("Base file not specified");
			
			if ($propertyName == "")
				throw new Exception("Property Name not specified");
			
			if ($propertyValue == "")
				throw new Exception("Property Value not specified");
			
			//build URI to merge Docs
			$strURI = Product::$BaseProductUri . "/words/" . $this->FileName . "/documentProperties/" . $propertyName;
			
			$put_data_arr['Value'] = $propertyValue;
			
			$put_data = json_encode($put_data_arr);
			
			//sign URI
			$signedURI = Utils::Sign($strURI);
	
			$responseStream = Utils::processCommand($signedURI, "PUT", "json", $put_data);
			
			$json = json_decode($responseStream);						
			
			if($json->Code == 200)
				return $json->DocumentProperty;
			else
				return false;			
					
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	
	public function ProtectDocument($password, $protectionType = 'AllowOnlyComments') {
        try {
            if ($this->FileName == "") {
                throw new Exception("Base file not specified");
            }
            if ($password == "") {
                throw new Exception("Please Specify A Password");
            }
            $fieldsArray = array('Password' => $password, 'ProtectionType' => $protectionType);
            $json = json_encode($fieldsArray);
            $strURI = Product::$BaseProductUri . "/words/" . $this->FileName . "/protection";
            $signedURI = Utils::Sign($strURI);
            $responseStream = Utils::processCommand($signedURI, "PUT", "json", $json);
            $v_output = Utils::ValidateOutput($responseStream);
            if ($v_output === "") {
                $strURI = Product::$BaseProductUri . "/storage/file/" . $this->FileName;
                $signedURI = Utils::Sign($strURI);
                $responseStream = Utils::processCommand($signedURI, "GET", "", "");
                $outputFile = SaasposeApp::$OutPutLocation . $this->FileName;
                Utils::saveFile($responseStream, $outputFile);
                return $outputFile;
            }
            else
                return $v_output;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function UnprotectDocument($password, $protectionType = 'AllowOnlyComments') {
        try {
            if ($this->FileName == "") {
                throw new Exception("Base file not specified");
            }
            if ($password == "") {
                throw new Exception("Please Specify A Password");
            }
            $fieldsArray = array('Password' => $password, 'ProtectionType' => $protectionType);
            $json = json_encode($fieldsArray);
            $strURI = Product::$BaseProductUri . "/words/" . $this->FileName . "/protection";
            $signedURI = Utils::Sign($strURI);
            $responseStream = Utils::processCommand($signedURI, "DELETE", "json", $json);
            $v_output = Utils::ValidateOutput($responseStream);
            if ($v_output === "") {
                $strURI = Product::$BaseProductUri . "/storage/file/" . $this->FileName;
                $signedURI = Utils::Sign($strURI);
                $responseStream = Utils::processCommand($signedURI, "GET", "", "");
                $outputFile = SaasposeApp::$OutPutLocation . $this->FileName;
                Utils::saveFile($responseStream, $outputFile);
                return $outputFile;
            }
            else
                return $v_output;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function UpdateProtection($old_password, $new_password, $protection_type = 'AllowOnlyComments') {
        try {
            if ($this->FileName == "") {
                throw new Exception("Base file not specified");
            }
            if ($old_password == "") {
                throw new Exception("Please Specify Old Password");
            }
            if ($new_password == "") {
                throw new Exception("Please Specify New Password");
            }
            $fieldsArray = array('Password' => $old_password, 'NewPassword' => $new_password, 'ProtectionType' => $protection_type);
            $json = json_encode($fieldsArray);
            $strURI = Product::$BaseProductUri . "/words/" . $this->FileName . "/protection";
            $signedURI = Utils::Sign($strURI);
            $responseStream = Utils::processCommand($signedURI, "POST", "json", $json);
            $v_output = Utils::ValidateOutput($responseStream);
            if ($v_output === "") {
                $strURI = Product::$BaseProductUri . "/storage/file/" . $this->FileName;
                $signedURI = Utils::Sign($strURI);
                $responseStream = Utils::processCommand($signedURI, "GET", "", "");
                $outputFile = SaasposeApp::$OutPutLocation . $this->FileName;
                Utils::saveFile($responseStream, $outputFile);
                return $outputFile;
            }
            else
                return $v_output;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
	
	/*
    * Delete a document property
	@param string $propertyName
	*/		
	public function DeleteProperty($propertyName){
		try{
			//check whether files are set or not
			if ($this->FileName == "")
				throw new Exception("Base file not specified");
			
			if ($propertyName == "")
				throw new Exception("Property Name not specified");
			
			//build URI to merge Docs
			$strURI = Product::$BaseProductUri . "/words/" . $this->FileName . "/documentProperties/" . $propertyName;
			
			//sign URI
			$signedURI = Utils::Sign($strURI);
	
			$responseStream = Utils::processCommand($signedURI, "DELETE", "", "");
			
			$json = json_decode($responseStream);									
			
			if($json->Code == 200)
				return true;
			else
				return false;			
					
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	
	/*
    * Get Document's properties
	*/		
	public function GetProperties(){
		try{
			//check whether files are set or not
			if ($this->FileName == "")
				throw new Exception("Base file not specified");
						
			
			//build URI to merge Docs
			$strURI = Product::$BaseProductUri . "/words/" . $this->FileName . "/documentProperties";
			
			//sign URI
			$signedURI = Utils::Sign($strURI);
	
			$responseStream = Utils::processCommand($signedURI, "GET", "", "");
			
			$json = json_decode($responseStream);									
			
			
			if($json->Code == 200)
				return $json->DocumentProperties->List;
			else
				return false;
					
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	
	/*
    * Convert Document to different file format without using storage
	* $param string $inputPath
	* @param string $outputPath
	* @param string $outputFormat
	*/
	public function ConvertLocalFile($inputPath="",$outputPath="",$outputFormat="")
	{
		try
		{
			//check whether file is set or not
			if ($inputPath == "")
				throw new Exception("No file name specified");							
			
			if ($outputFormat == "")
				throw new Exception("output format not specified");
				
				   
			$strURI = Product::$BaseProductUri . "/words/convert?format=" . $outputFormat;
			
			if(!file_exists($inputPath))
			{
				throw new Exception("input file doesn't exist.");
			}
						
			
			$signedURI = Utils::Sign($strURI);

			$responseStream = Utils::uploadFileBinary($signedURI, $inputPath , "xml");			
			
			$v_output = Utils::ValidateOutput($responseStream);			
 
			if ($v_output === "") 
			{
				
				$save_format = $outputFormat;
				
				if($outputPath == "")
				{
					$outputPath = Utils::getFileName($inputPath). "." . $save_format;
				}
					
				Utils::saveFile($responseStream, SaasposeApp::$OutPutLocation . $outputPath);
				return true;
			} 
			else 
				return $v_output;
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}  
	}
	
}