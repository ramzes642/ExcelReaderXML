<?php
class ExcelReaderXML {

	/**
	 * @var XMLReader
	 */
	private $xml;
	private $tagStack=array();

	public function __construct($filename, $encoding = null) {
		$this->xml = new XMLReader();
		$this->xml->open($filename, $encoding);
	}

	/**
	 * Read to first spreadsheet declaration, returns it's name
	 * @return string Spreadsheet name
	 */
	public function readToSpreadsheet() {
		$tag = '';
		while ($this->xml->read()) {
			if ($this->xml->nodeType == XMLReader::ELEMENT && $this->xml->name == 'Worksheet') {
				if ($this->xml->hasAttributes) {
					while ($this->xml->moveToNextAttribute()) {
						if ($this->xml->name == 'ss:Name')
							return $this->xml->value;
					}
				}
			}
		}
		return false; // readed to the end and no spreadsheets found
	}

	private function readAttrs() {
		$attr = array();
		if ($this->xml->hasAttributes) {
			while ($this->xml->moveToNextAttribute()) {
				$attr[$this->xml->name] = $this->xml->value;
			}
		}
		return $attr;
	}

	public function readRow() {
		$colid = 0;
		$row = array();
		$stackable = array('Table', 'Row', 'Cell', 'Data');
		while ($this->xml->read()) {
			// fill up tag stack
			if (in_array($this->xml->name, $stackable)) {
				if ($this->xml->nodeType == XMLReader::ELEMENT)
					array_unshift($this->tagStack, $this->xml->name);
				if ($this->xml->nodeType == XMLReader::END_ELEMENT)
					array_shift($this->tagStack);
			}
			
			// if we find close tag - that means that spreasheet ended
			if ($this->xml->nodeType == XMLReader::END_ELEMENT && $this->xml->name == 'Worksheet')
				return false;
			// if we find end of row - return collected data
			if ($this->xml->nodeType == XMLReader::END_ELEMENT && $this->xml->name == 'Row')
				return $row;
			
			// if we find ening cell tag - than iterate column index
			if ($this->xml->nodeType == XMLReader::END_ELEMENT && $this->xml->name == 'Cell')
				$colid++;
			
			// open tags
			if ($this->xml->nodeType == XMLReader::ELEMENT) {
				if ($this->xml->name == 'Cell' && $this->tagStack[1] == 'Row')
					$row['CellInfo'][$colid] = $this->readAttrs();
				if ($this->xml->name == 'Data' && $this->tagStack[1] == 'Cell' && $this->tagStack[2] == 'Row') {
					$row['CellInfo'][$colid] += $this->readAttrs(); // append data types
					$row['Data'][$colid] = html_entity_decode($this->xml->readInnerXml());
				}
			} // nodeType == XMLReader::ELEMENT
		}
		return false;
	}
}