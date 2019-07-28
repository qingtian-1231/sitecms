<?php
namespace woo\utility;

use woo\utility\Hash;
use woo\utility\CdataXml;

class XmlExport
{
    public static function fromArray($filename, $data, $header, $sheetname = 'Sheet1')
    {
		$formatted_data = [];        		
		if($header){
			foreach($header as $field => &$info){
				if (!is_array($info)) {
				    $info = ['title' => $info];
				}
				$formatted_data['Row'][0]['Cell'][] = array(
					'Data' => array(
						'$' => $info['title'] ? $info['title'] : $field,
						'@ss:Type' => 'String',
					),
				);
			}
		}
		        
		foreach ($data as $index => &$item){
			$row = Hash::flatten($item);
			foreach ($header as $xfield => $xinfo) {
				$val = $row[$xfield];
				if (is_callable($xinfo['callback'])) {
					$formatted_data['Row'][$index + 1]['Cell'][] = array(
						'Data'=>array(
							$info['cdata'] ? '$' : '@' => strval(call_user_func($xinfo['callback'], $val, $row)),
							'@ss:Type' => 'String'
						)
					);
				} else {
					$formatted_data['Row'][$index+1]['Cell'][] = array(
						'Data' => array(
							$info['cdata'] ? '$' : '@' => strval($val),
							'@ss:Type'=>'String'
						)
					);
				}
			}
		}
		$xmlArray = array(
			'Workbook' => array(
				'xmlns:' => 'urn:schemas-microsoft-com:office:spreadsheet',
				'xmlns:x' => 'urn:schemas-microsoft-com:office:excel',
				'xmlns:ss' => 'urn:schemas-microsoft-com:office:spreadsheet',
				'xmlns:html' => 'http://www.w3.org/TR/REC-html40',
				'Worksheet' => array(
					'@ss:Name' => $sheetname,
					'Table' => $formatted_data
				)
			)
		);
        
		return CdataXml::fromArray($xmlArray)->asXML();
        
	}
    
    public function sendXml($filename, $data, $header = [], $sheetname = 'Sheet1')
    {   
        $xml = call_user_func_array(['self', 'fromArray'], func_get_args());
        
		$ua=$_SERVER['HTTP_USER_AGENT'];
		if (preg_match("/MSIE/", $ua)) {
			$encoded_filename = urlencode($filename);  
			$encoded_filename = str_replace("+", "%20", $encoded_filename);  
		}else {  
			$encoded_filename = $filename;
		}
        
        header("Content-Type: application/vnd.ms-excel; name='excel'");
        header("Content-type: application/octet-stream"); 
        header("Content-Disposition: attachment; filename=" . $encoded_filename .'.xls');
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
        header("Pragma: no-cache");
        header("Expires: 0"); 
        exit($xml);
    }
}