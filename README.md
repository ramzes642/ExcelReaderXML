ExcelReaderXML
==============

Reads XML files generated by Microsoft Excel

Inspired by simple and fast ExcelWriterXML[http://excelwriterxml.sourceforge.net/]

### Advantages:
* Uses XMLReader and can parse even very large files
* Fast and simple, and memory conservative

### Example:

	<?php
		include "ExcelReaderXML.php";

		$xml = new ExcelReaderXML('spreadsheet.xml');

		while ($spreadsheet_name = $xml->readSpreadsheet()) {
			echo "Spreadsheet: $spreadsheet_name\n";
			while ($row = $xml->readRow()) {
				print_r($row);
			}
		}
	?>

### Not done:
* Excel format check (in case of invalid file - library just don't return anything)
* You know what
