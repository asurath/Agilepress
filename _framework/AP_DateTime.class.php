<?php
//////////////////////////////////////////////
//
// AP_DateTime class
//
// Handles date related functiaonlity and formatting
//////////////////////////////////////////////

function AP_DateTimeErrorHandler() {}
class AP_DateTime extends DateTime {

	protected $blnDateNull = true;
	protected $blnTimeNull = true;
	public static $Translate = false;

	const Now = 'now';
	const FormatIso = 'YYYY-MM-DD hhhh:mm:ss';
	const FormatIsoCompressed = 'YYYYMMDDhhhhmmss';
	const FormatDisplayDate = 'MMM DD YYYY';
	const FormatDisplayDateFull = 'DDD, MMMM D, YYYY';
	const FormatDisplayDateTime = 'MMM DD YYYY hh:mm zz';
	const FormatDisplayDateTimeFull = 'DDDD, MMMM D, YYYY, h:mm:ss zz';
	const FormatDisplayTime = 'hh:mm:ss zz';
	const FormatRfc822 = 'DDD, DD MMM YYYY hhhh:mm:ss ttt';

	public static $DefaultFormat = AP_DateTime::FormatDisplayDateTime;
	public static $DefaultTimeOnlyFormat = AP_DateTime::FormatDisplayTime;
	public static $DefaultDateOnlyFormat = AP_DateTime::FormatDisplayDate;

	/**
	 * Returns a new AP_DateTime object that's set to "Now"
	 * Set blnTimeValue to true (default) for a DateTime, and set blnTimeValue to false for just a Date
	 *
	 * @param boolean $blnTimeValue whether or not to include the time value
	 * @return AP_DateTime the current date and/or time
	 */
	public static function Now($blnTimeValue = true) {
		$dttToReturn = new AP_DateTime(AP_DateTime::Now);
		if (!$blnTimeValue) {
			$dttToReturn->blnTimeNull = true;
			$dttToReturn->ReinforceNullProperties();
		}
		return $dttToReturn;
	}



	public function __construct($mixValue = null, DateTimeZone $objTimeZone = null) {

		if (strtolower($mixValue) == AP_DateTime::Now) {
			if ($objTimeZone)
				parent::__construct('now', $objTimeZone);
			else
				parent::__construct('now');
			$this->blnDateNull = false;
			$this->blnTimeNull = false;

			// Null or No Value
		} else if (!$mixValue) {
			// Set to "null date"
			// And Do Nothing Else -- Default Values are already set to Nulled out
			if ($objTimeZone)
				parent::__construct('2000-01-01 00:00:00', $objTimeZone);
			else
				parent::__construct('2000-01-01 00:00:00');

			// Parse the Value string
		} else {

			$intTimestamp = null;
			$blnValid = false;
			set_error_handler('AP_DateTimeErrorHandler');
			try {
				if ($objTimeZone)
					$blnValid = parent::__construct($mixValue, $objTimeZone);
				else
					$blnValid = parent::__construct($mixValue);
			} catch (Exception $objExc) {}
			if ($blnValid !== false)
				$intTimestamp = parent::format('U');
			restore_error_handler();
				
			// Valid Value String
			if ($intTimestamp) {
				// To deal with "Tues" and date skipping bug in PHP 5.2
				if ($objTimeZone)
					parent::__construct(date('Y-m-d H:i:s', parent::format('U')), $objTimeZone);
				else
					parent::__construct(date('Y-m-d H:i:s', parent::format('U')));
					
				// We MUST assume that Date isn't null
				$this->blnDateNull = false;
					
				// Update Time Null Value if Time was Specified
				if (strpos($mixValue, ':') !== false)
					$this->blnTimeNull = false;
					
				// Timestamp-based Value string
			} else if (is_numeric($mixValue)) {
				if ($objTimeZone)
					parent::__construct(date('Y-m-d H:i:s', $mixValue), $objTimeZone);
				else
					parent::__construct(date('Y-m-d H:i:s', $mixValue));
					
				$this->blnTimeNull = false;
				$this->blnDateNull = false;
					
				// Null Date
			} else {
				// Set to "null date"
				// And Do Nothing Else -- Default Values are already set to Nulled out
				if ($objTimeZone)
					parent::__construct('2000-01-01 00:00:00', $objTimeZone);
				else
					parent::__construct('2000-01-01 00:00:00');
			}
		}

	}

	public function __toString() {
		// For PHP 5.3 Compatability
		$strArgumentArray = func_get_args();

		if (count($strArgumentArray) >= 1)
			$strFormat = $strArgumentArray[0];
		else
			$strFormat = null;

		return $this->ToString($strFormat);
	}

	/**
	 * Outputs the date as a string given the format strFormat.  By default,
	 * it will return as AP_DateTime::FormatDisplayDate "MMM DD YYYY", e.g. Mar 20 1977.
	 *
	 * Properties of strFormat are (using Sunday, March 2, 1977 at 1:15:35 pm
	 * in the following examples):
	 *
	 *	M - Month as an integer (e.g., 3)
	 *	MM - Month as an integer with leading zero (e.g., 03)
	 *	MMM - Month as three-letters (e.g., Mar)
	 *	MMMM - Month as full name (e.g., March)
	 *
	 *	D - Day as an integer (e.g., 2)
	 *	DD - Day as an integer with leading zero (e.g., 02)
	 *	DDD - Day of week as three-letters (e.g., Wed)
	 *	DDDD - Day of week as full name (e.g., Wednesday)
	 *
	 *	YY - Year as a two-digit integer (e.g., 77)
	 *	YYYY - Year as a four-digit integer (e.g., 1977)
	 *
	 *	h - Hour as an integer in 12-hour format (e.g., 1)
	 *	hh - Hour as an integer in 12-hour format with leading zero (e.g., 01)
	 *	hhh - Hour as an integer in 24-hour format (e.g., 13)
	 *	hhhh - Hour as an integer in 24-hour format with leading zero (e.g., 13)
	 *
	 *	mm - Minute as a two-digit integer
	 *
	 *	ss - Second as a two-digit integer
	 *
	 *	z - "pm" or "am"
	 *	zz - "PM" or "AM"
	 *	zzz - "p.m." or "a.m."
	 *	zzzz - "P.M." or "A.M."
	 *
	 *  ttt - Timezone Abbreviation as a three-letter code (e.g. PDT, GMT)
	 *  tttt - Timezone Identifier (e.g. America/Los_Angeles)
	 *
	 * @param string $strFormat the format of the date
	 * @return string the formatted date as a string
	 */
	public function ToString($strFormat = null) {
		$this->ReinforceNullProperties();
		if (is_null($strFormat)) {
			if ($this->IsTimeNull())
				$strFormat = AP_DateTime::$DefaultDateOnlyFormat;
			else if ($this->IsDateNull())
				$strFormat = AP_DateTime::$DefaultTimeOnlyFormat;
			else
				$strFormat = AP_DateTime::$DefaultFormat;
		}

		preg_match_all('/(?(?=D)([D]+)|(?(?=M)([M]+)|(?(?=Y)([Y]+)|(?(?=h)([h]+)|(?(?=m)([m]+)|(?(?=s)([s]+)|(?(?=z)([z]+)|(?(?=t)([t]+)|))))))))/', $strFormat, $strArray);
		$strArray = $strArray[0];
		$strToReturn = '';

		$intStartPosition = 0;
		for ($intIndex = 0; $intIndex < count($strArray); $intIndex++) {
			$strToken = trim($strArray[$intIndex]);
			if ($strToken) {
				$intEndPosition = strpos($strFormat, $strArray[$intIndex], $intStartPosition);
				$strToReturn .= substr($strFormat, $intStartPosition, $intEndPosition - $intStartPosition);
				$intStartPosition = $intEndPosition + strlen($strArray[$intIndex]);

				switch ($strArray[$intIndex]) {
					case 'M':
						$strToReturn .= parent::format('n');
						break;
					case 'MM':
						$strToReturn .= parent::format('m');
						break;
					case 'MMM':
						$strToReturn .= parent::format('M');
						break;
					case 'MMMM':
						$strToReturn .= parent::format('F');
						break;
							
					case 'D':
						$strToReturn .= parent::format('j');
						break;
					case 'DD':
						$strToReturn .= parent::format('d');
						break;
					case 'DDD':
						$strToReturn .= parent::format('D');
						break;
					case 'DDDD':
						$strToReturn .= parent::format('l');
						break;
							
					case 'YY':
						$strToReturn .= parent::format('y');
						break;
					case 'YYYY':
						$strToReturn .= parent::format('Y');
						break;
							
					case 'h':
						$strToReturn .= parent::format('g');
						break;
					case 'hh':
						$strToReturn .= parent::format('h');
						break;
					case 'hhh':
						$strToReturn .= parent::format('G');
						break;
					case 'hhhh':
						$strToReturn .= parent::format('H');
						break;

					case 'mm':
						$strToReturn .= parent::format('i');
						break;
							
					case 'ss':
						$strToReturn .= parent::format('s');
						break;
							
					case 'z':
						$strToReturn .= parent::format('a');
						break;
					case 'zz':
						$strToReturn .= parent::format('A');
						break;
					case 'zzz':
						$strToReturn .= sprintf('%s.m.', substr(parent::format('a'), 0, 1));
						break;
					case 'zzzz':
						$strToReturn .= sprintf('%s.M.', substr(parent::format('A'), 0, 1));
						break;

					case 'ttt':
						$strToReturn .= parent::format('T');
						break;
					case 'tttt':
						$strToReturn .= parent::format('e');
						break;
					case 'ttttt':
						$strToReturn .= parent::format('O');
						break;

					default:
						$strToReturn .= $strArray[$intIndex];
				}
			}
		}

		if ($intStartPosition < strlen($strFormat))
			$strToReturn .= substr($strFormat, $intStartPosition);

		return $strToReturn;
	}
	public function IsDateNull() {
		return $this->blnDateNull;
	}
	public function IsNull() {
		return ($this->blnDateNull && $this->blnTimeNull);
	}
	public function IsTimeNull() {
		return $this->blnTimeNull;
	}

	public function setTime($intHour, $intMinute, $intSecond = null) {
		// For compatibility with PHP 5.3
		if (is_null($intSecond)) $intSecond = 0;

		// If HOUR or MINUTE is NULL...
		if (is_null($intHour) || is_null($intMinute)) {
			parent::setTime($intHour, $intMinute, $intSecond);
			$this->blnTimeNull = true;
			return $this;
		}

		if (is_null($intHour))
			$this->blnTimeNull = true;
		else
			$this->blnTimeNull = false;

		parent::setTime($intHour, $intMinute, $intSecond);
		return $this;
	}

	public function setDate($intYear, $intMonth, $intDay) {

		if (is_null($intYear))
			$this->blnDateNull = true;
		else
			$this->blnDateNull = false;

		parent::setDate($intYear, $intMonth, $intDay);
		return $this;
	}
	protected function ReinforceNullProperties() {
		if ($this->blnDateNull)
			parent::setDate(2000, 1, 1);
		if ($this->blnTimeNull)
			parent::setTime(0, 0, 0);
	}

	public function IsEqualTo(QDateTime $dttCompare) {
		// All comparison operations MUST have operands with matching Date Nullstates
		if ($this->blnDateNull != $dttCompare->blnDateNull)
			return false;

		// If mismatched Time nullstates, then only compare the Date portions
		if ($this->blnTimeNull != $dttCompare->blnTimeNull) {
			// Let's "Null Out" the Time
			$dttThis = new QDateTime($this);
			$dttThat = new QDateTime($dttCompare);
			$dttThis->Hour = null;
			$dttThat->Hour = null;

			// Return the Result
			return ($dttThis->Timestamp == $dttThat->Timestamp);
		} else {
			// Return the Result for the both Date and Time components
			return ($this->Timestamp == $dttCompare->Timestamp);
		}
	}

	public function IsEarlierThan(QDateTime $dttCompare) {
		// All comparison operations MUST have operands with matching Date Nullstates
		if ($this->blnDateNull != $dttCompare->blnDateNull)
			return false;

		// If mismatched Time nullstates, then only compare the Date portions
		if ($this->blnTimeNull != $dttCompare->blnTimeNull) {
			// Let's "Null Out" the Time
			$dttThis = new QDateTime($this);
			$dttThat = new QDateTime($dttCompare);
			$dttThis->Hour = null;
			$dttThat->Hour = null;

			// Return the Result
			return ($dttThis->Timestamp < $dttThat->Timestamp);
		} else {
			// Return the Result for the both Date and Time components
			return ($this->Timestamp < $dttCompare->Timestamp);
		}
	}

	public function IsEarlierOrEqualTo(QDateTime $dttCompare) {
		// All comparison operations MUST have operands with matching Date Nullstates
		if ($this->blnDateNull != $dttCompare->blnDateNull)
			return false;

		// If mismatched Time nullstates, then only compare the Date portions
		if ($this->blnTimeNull != $dttCompare->blnTimeNull) {
			// Let's "Null Out" the Time
			$dttThis = new QDateTime($this);
			$dttThat = new QDateTime($dttCompare);
			$dttThis->Hour = null;
			$dttThat->Hour = null;

			// Return the Result
			return ($dttThis->Timestamp <= $dttThat->Timestamp);
		} else {
			// Return the Result for the both Date and Time components
			return ($this->Timestamp <= $dttCompare->Timestamp);
		}
	}

	public function IsLaterThan(QDateTime $dttCompare) {
		// All comparison operations MUST have operands with matching Date Nullstates
		if ($this->blnDateNull != $dttCompare->blnDateNull)
			return false;

		// If mismatched Time nullstates, then only compare the Date portions
		if ($this->blnTimeNull != $dttCompare->blnTimeNull) {
			// Let's "Null Out" the Time
			$dttThis = new QDateTime($this);
			$dttThat = new QDateTime($dttCompare);
			$dttThis->Hour = null;
			$dttThat->Hour = null;

			// Return the Result
			return ($dttThis->Timestamp > $dttThat->Timestamp);
		} else {
			// Return the Result for the both Date and Time components
			return ($this->Timestamp > $dttCompare->Timestamp);
		}
	}

	public function IsLaterOrEqualTo(QDateTime $dttCompare) {
		// All comparison operations MUST have operands with matching Date Nullstates
		if ($this->blnDateNull != $dttCompare->blnDateNull)
			return false;

		// If mismatched Time nullstates, then only compare the Date portions
		if ($this->blnTimeNull != $dttCompare->blnTimeNull) {
			// Let's "Null Out" the Time
			$dttThis = new AP_DateTime($this);
			$dttThat = new AP_DateTime($dttCompare);
			$dttThis->Hour = null;
			$dttThat->Hour = null;

			// Return the Result
			return ($dttThis->Timestamp >= $dttThat->Timestamp);
		} else {
			// Return the Result for the both Date and Time components
			return ($this->Timestamp >= $dttCompare->Timestamp);
		}
	}

	/**
	 *
	 * @param AP_DateTime $dttDateTime
	 * @return AP_DateTimeSpan
	 */
	public function Difference(AP_DateTime $dttDateTime) {
		$intDifference = $this->Timestamp - $dttDateTime->Timestamp;
		return new AP_DateTimeSpan($intDifference);
	}

	public function Add($dtsSpan){
		if ($dtsSpan instanceof AP_DateTimeSpan) {
			// And add the Span Second count to it
			$this->Timestamp = $this->Timestamp + $dtsSpan->Seconds;
			return $this;
		} else if ($dtsSpan instanceof DateInterval) {
			return parent::add($dtsSpan);
		}
	}

	public function AddSeconds($intSeconds){
		$this->Second += $intSeconds;
		return $this;
	}

	public function AddMinutes($intMinutes){
		$this->Minute += $intMinutes;
		return $this;
	}

	public function AddHours($intHours){
		$this->Hour += $intHours;
		return $this;
	}

	public function AddDays($intDays){
		$this->Day += $intDays;
		return $this;
	}

	public function AddMonths($intMonths){
		$this->Month += $intMonths;
		return $this;
	}

	public function AddYears($intYears){
		$this->Year += $intYears;
		return $this;
	}

	public function Modify($mixValue) {
		parent::modify($mixValue);
		return $this;
	}

	public function __get($strName) {
		$this->ReinforceNullProperties();

		switch ($strName) {
			case 'Month':
				if ($this->blnDateNull)
					return null;
				else
					return (int) parent::format('m');

			case 'Day':
				if ($this->blnDateNull)
					return null;
				else
					return (int) parent::format('d');

			case 'Year':
				if ($this->blnDateNull)
					return null;
				else
					return (int) parent::format('Y');

			case 'Hour':
				if ($this->blnTimeNull)
					return null;
				else
					return (int) parent::format('H');

			case 'Minute':
				if ($this->blnTimeNull)
					return null;
				else
					return (int) parent::format('i');

			case 'Second':
				if ($this->blnTimeNull)
					return null;
				else
					return (int) parent::format('s');

			case 'Timestamp':
				// Until PHP fixes a bug where lowest int is int(-2147483648) but lowest float/double is (-2147529600)
				// We return as a "double"
				return (double) parent::format('U');
		}
	}

	public function __set($strName, $mixValue) {
		try {
			switch ($strName) {
				case 'Month':
					if ($this->blnDateNull && (!is_null($mixValue)))
						throw new AP_Exception('Cannot set the Month property on a null date.  Use SetDate().');
					if (is_null($mixValue)) {
						$this->blnDateNull = true;
						$this->ReinforceNullProperties();
						return null;
					}
					parent::setDate(parent::format('Y'), $mixValue, parent::format('d'));
					return $mixValue;

				case 'Day':
					if ($this->blnDateNull && (!is_null($mixValue)))
						throw new AP_Exception('Cannot set the Day property on a null date.  Use SetDate().');
					if (is_null($mixValue)) {
						$this->blnDateNull = true;
						$this->ReinforceNullProperties();
						return null;
					}
					parent::setDate(parent::format('Y'), parent::format('m'), $mixValue);
					return $mixValue;

				case 'Year':
					if ($this->blnDateNull && (!is_null($mixValue)))
						throw new AP_Exception('Cannot set the Year property on a null date.  Use SetDate().');
					if (is_null($mixValue)) {
						$this->blnDateNull = true;
						$this->ReinforceNullProperties();
						return null;
					}
					parent::setDate($mixValue, parent::format('m'), parent::format('d'));
					return $mixValue;

				case 'Hour':
					if ($this->blnTimeNull && (!is_null($mixValue)))
						throw new AP_Exception('Cannot set the Hour property on a null time.  Use SetTime().');
					if (is_null($mixValue)) {
						$this->blnTimeNull = true;
						$this->ReinforceNullProperties();
						return null;
					}
					parent::setTime($mixValue, parent::format('i'), parent::format('s'));
					return $mixValue;

				case 'Minute':
					if ($this->blnTimeNull && (!is_null($mixValue)))
						throw new AP_Exception('Cannot set the Minute property on a null time.  Use SetTime().');
					if (is_null($mixValue)) {
						$this->blnTimeNull = true;
						$this->ReinforceNullProperties();
						return null;
					}
					parent::setTime(parent::format('H'), $mixValue, parent::format('s'));
					return $mixValue;

				case 'Second':
					if ($this->blnTimeNull && (!is_null($mixValue)))
						throw new AP_Exception('Cannot set the Second property on a null time.  Use SetTime().');
					if (is_null($mixValue)) {
						$this->blnTimeNull = true;
						$this->ReinforceNullProperties();
						return null;
					}
					parent::setTime(parent::format('H'), parent::format('i'), $mixValue);
					return $mixValue;

				case 'Timestamp':
					$mixValue = QType::Cast($mixValue, QType::Integer);
					$this->blnDateNull = false;
					$this->blnTimeNull = false;

					$this->SetDate(date('Y', $mixValue), date('m', $mixValue), date('d', $mixValue));
					$this->SetTime(date('H', $mixValue), date('i', $mixValue), date('s', $mixValue));
					return $mixValue;
			}
		} catch (AP_Exception $objExc) {
			throw $objExc;
		}
	}
}