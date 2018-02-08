<?php
//////////////////////////////////////////////
//
// AP_DateTimeSpan class
//
// Handles time span formatting and functionality
//////////////////////////////////////////////

class AP_DateTimeSpan {
	protected $intSeconds;

	/* From: http://tycho.usno.navy.mil/leapsec.html:
	 This definition was ratified by the Eleventh General Conference on Weights and Measures in 1960.
	Reference to the year 1900  does not mean that this is the epoch of a mean solar day of 86,400 seconds.
	Rather, it is the epoch of the tropical year of 31,556,925.9747 seconds of ephemeris time.
	Ephemeris Time (ET) was defined as the measure of time that brings the observed positions of the celestial
	bodies into accord with the Newtonian dynamical theory of motion.
	*/
	const SECONDS_PER_YEAR	= 31556926;

	// Assume 30 Days per Month
	const SECONDS_PER_MONTH 	= 2592000;
	const SECONDS_PER_DAY 	= 86400;
	const SECONDS_PER_HOUR 	= 3600;
	const SECONDS_PER_MINUTE 	= 60;

	public function __construct($intSeconds = 0) {
		$this->intSeconds = $intSeconds;
	}

	/*
	 Is functions
	*/

	/**
	 * Checks if the current DateSpan is positive
	 *
	 * @return boolean
	 */
	public function IsPositive(){
		return ($this->intSeconds > 0);
	}

	/**
	 * Checks if the current DateSpan is negative
	 *
	 * @return boolean
	 */
	public function IsNegative(){
		return ($this->intSeconds < 0);
	}

	/**
	 * Checks if the current DateSpan is zero
	 *
	 * @return boolean
	 */
	public function IsZero(){
		return ($this->intSeconds == 0);
	}

	/**
	 * Calculates the difference between this DateSpan and another DateSpan
	 *
	 * @param AP_DateTimeSpan $dtsSpan
	 * @return new AP_DateTimeSpan
	 */
	public function Difference(AP_DateTimeSpan $dtsSpan){
		$intDifference = $this->Seconds - $dtsSpan->Seconds;
		$dtsDateSpan = new AP_DateTimeSpan();
		$dtsDateSpan->AddSeconds($intDifference);
		return $dtsDateSpan;
	}

	/*
	 SetFrom methods
	*/

	/**
	 * Sets current AP_DateTimeSpan to the difference between two QDateTime objects
	 *
	 * @param QDateTime $dttFrom
	 * @param QDateTime $dttTo
	 */
	public function SetFromBSDateTime(AP_DateTime $dttFrom, AP_DateTime $dttTo){
		$this->Add($dttFrom->Difference($dttTo));
	}

	/*
	 Add methods
	*/

	/**
	 * Adds an amount of seconds to the current AP_DateTimeSpan
	 *
	 * @param int $intSeconds
	 */
	public function AddSeconds($intSeconds){
		$this->intSeconds = $this->intSeconds + $intSeconds;
	}

	/**
	 * Adds an amount of minutes to the current AP_DateTimeSpan
	 *
	 * @param int $intMinutes
	 */
	public function AddMinutes($intMinutes){
		$this->intSeconds = $this->intSeconds + ($intMinutes * AP_DateTimeSpan::SECONDS_PER_MINUTE);
	}

	/**
	 * Adds an amount of hours to the current AP_DateTimeSpan
	 *
	 * @param int $intHours
	 */
	public function AddHours($intHours){
		$this->intSeconds = $this->intSeconds + ($intHours * AP_DateTimeSpan::SECONDS_PER_HOUR);
	}

	/**
	 * Adds an amount of days to the current AP_DateTimeSpan
	 *
	 * @param int $intDays
	 */
	public function AddDays($intDays){
		$this->intSeconds = $this->intSeconds + ($intDays * AP_DateTimeSpan::SECONDS_PER_DAY);
	}

	/**
	 * Adds an amount of months to the current AP_DateTimeSpan
	 *
	 * @param int $intMonths
	 */
	public function AddMonths($intMonths){
		$this->intSeconds = $this->intSeconds + ($intMonths * AP_DateTimeSpan::SECONDS_PER_MONTH);
	}

	/*
	 Get methods
	*/

	/**
	 * Calculates the total whole years in the current AP_DateTimeSpan
	 *
	 * @return int
	 */
	protected function GetYears() {
		$intSecondsPerYear = ($this->IsPositive()) ? AP_DateTimeSpan::SECONDS_PER_YEAR : ((-1) * AP_DateTimeSpan::SECONDS_PER_YEAR);
		$intYears = floor($this->intSeconds / $intSecondsPerYear);
		if ($this->IsNegative()) $intYears = (-1) * $intYears;
		return $intYears;
	}

	/**
	 * Calculates the total whole months in the current AP_DateTimeSpan
	 *
	 * @return int
	 */
	protected function GetMonths(){
		$intSecondsPerMonth = ($this->IsPositive()) ? AP_DateTimeSpan::SECONDS_PER_MONTH : ((-1) * AP_DateTimeSpan::SECONDS_PER_MONTH);
		$intMonths = floor($this->intSeconds / $intSecondsPerMonth);
		if($this->IsNegative()) $intMonths = (-1) * $intMonths;
		return $intMonths;
	}

	/**
	 * Calculates the total whole days in the current AP_DateTimeSpan
	 *
	 * @return int
	 */
	protected function GetDays(){
		$intSecondsPerDay = ($this->IsPositive()) ? AP_DateTimeSpan::SECONDS_PER_DAY : ((-1) * AP_DateTimeSpan::SECONDS_PER_DAY);
		$intDays = floor($this->intSeconds / $intSecondsPerDay);
		if($this->IsNegative()) $intDays = (-1) * $intDays;
		return $intDays;
	}

	/**
	 * Calculates the total whole hours in the current AP_DateTimeSpan
	 *
	 * @return int
	 */
	protected function GetHours(){
		$intSecondsPerHour = ($this->IsPositive()) ? AP_DateTimeSpan::SECONDS_PER_HOUR : ((-1) * AP_DateTimeSpan::SECONDS_PER_HOUR);
		$intHours = floor($this->intSeconds / $intSecondsPerHour);
		if($this->IsNegative()) $intHours = (-1) * $intHours;
		return $intHours;
	}

	/**
	 * Calculates the total whole minutes in the current AP_DateTimeSpan
	 *
	 * @return int
	 */
	protected function GetMinutes(){
		$intSecondsPerMinute = ($this->IsPositive()) ? AP_DateTimeSpan::SECONDS_PER_MINUTE : ((-1) * AP_DateTimeSpan::SECONDS_PER_MINUTE);
		$intMinutes = floor($this->intSeconds / $intSecondsPerMinute);
		if($this->IsNegative()) $intMinutes = (-1) * $intMinutes;
		return $intMinutes;
	}

	/*
	 DateMathSettings
	*/

	/**
	 * Adds a AP_DateTimeSpan to current AP_DateTimeSpan
	 *
	 * @param AP_DateTimeSpan $dtsSpan
	 */
	public function Add(AP_DateTimeSpan $dtsSpan){
		$this->intSeconds = $this->intSeconds + $dtsSpan->Seconds;
	}

	/**
	 * Subtracts a AP_DateTimeSpan to current AP_DateTimeSpan
	 *
	 * @param AP_DateTimeSpan $dtsSpan
	 */
	public function Subtract(AP_DateTimeSpan $dtsSpan){
		$this->intSeconds = $this->intSeconds - $dtsSpan->Seconds;
	}

	public function SimpleDisplay(){
		$arrTimearray = $this->GetTimearray();
		$strToReturn = null;

		if($arrTimearray['Years'] != 0) {
			$strFormat = ($arrTimearray['Years'] != 1) ? 'about %s years' :  'a year';
			$strToReturn = sprintf($strFormat, $arrTimearray['Years']);
		}
		elseif($arrTimearray['Months'] != 0){
			$strFormat = ($arrTimearray['Months'] != 1) ? 'about %s months' : 'a month';
			$strToReturn = sprintf($strFormat,$arrTimearray['Months']);
		}
		elseif($arrTimearray['Days'] != 0){
			$strFormat = ($arrTimearray['Days'] != 1) ? 'about %s days' : 'a day';
			$strToReturn = sprintf($strFormat,$arrTimearray['Days']);
		}
		elseif($arrTimearray['Hours'] != 0){
			$strFormat = ($arrTimearray['Hours'] != 1) ? 'about %s hours' : 'an hour';
			$strToReturn = sprintf($strFormat,$arrTimearray['Hours']);
		}
		elseif($arrTimearray['Minutes'] != 0){
			$strFormat = ($arrTimearray['Minutes'] != 1) ? '%s minutes' : 'a minute';
			$strToReturn = sprintf($strFormat,$arrTimearray['Minutes']);
		}
		elseif($arrTimearray['Seconds'] != 0 ){
			$strFormat = ($arrTimearray['Seconds'] != 1) ? '%s seconds' : 'a second';
			$strToReturn = sprintf($strFormat,$arrTimearray['Seconds']);
		}
			
		return $strToReturn;
	}


	/**
	 * Return an array of timeunints
	 *
	 *
	 * @return array of timeunits
	 */
	protected function GetTimearray(){
		$intSeSECONDS_PER_YEAR ($this->IsPositive()) ? AP_DateTimeSpan::SECONDS_PER_YEAR : ((-1) * AP_DateTimeSpan::SECONDS_PER_YEAR);
		$intSecondsPerMonth = ($this->IsPositive()) ? AP_DateTimeSpan::SECONDS_PER_MONTH : ((-1) * AP_DateTimeSpan::SECONDS_PER_MONTH);
		$intSecondsPerDay = ($this->IsPositive()) ? AP_DateTimeSpan::SECONDS_PER_DAY : ((-1) * AP_DateTimeSpan::SECONDS_PER_DAY);
		$intSecondsPerHour = ($this->IsPositive()) ? AP_DateTimeSpan::SECONDS_PER_HOUR : ((-1) * AP_DateTimeSpan::SECONDS_PER_HOUR);
		$intSecondsPerMinute = ($this->IsPositive()) ? AP_DateTimeSpan::SECONDS_PER_MINUTE : ((-1) * AP_DateTimeSpan::SECONDS_PER_MINUTE);
			
		$intSeconds = abs($this->intSeconds);

		$intYears = floor($intSeconds / AP_DateTimeSpan::SECONDS_PER_YEAR);
		$intSeconds = $intSeconds - ($intYears * AP_DateTimeSpan::SecondsPerYear);

		$intMonths = floor($intSeconds / AP_DateTimeSpan::SECONDS_PER_MONTH);
		$intSeconds = $intSeconds - ($intMonths * AP_DateTimeSpan::SECONDS_PER_MONTH);

		$intDays = floor($intSeconds / AP_DateTimeSpan::SECONDS_PER_DAY);
		$intSeconds = $intSeconds - ($intDays * AP_DateTimeSpan::SECONDS_PER_DAY);
			
		$intHours = floor($intSeconds / AP_DateTimeSpan::SECONDS_PER_HOUR);
		$intSeconds = $intSeconds - ($intHours * AP_DateTimeSpan::SECONDS_PER_HOUR);
			
		$intMinutes = floor($intSeconds / AP_DateTimeSpan::SECONDS_PER_MINUTE);
		$intSeconds = $intSeconds - ($intMinutes * AP_DateTimeSpan::SECONDS_PER_MINUTE);

		$intSeconds = $intSeconds;

		if($this->IsNegative()){
			// Turn values to negative
			$intYears = ((-1) * $intYears);
			$intMonths = ((-1) * $intMonths);
			$intDays = ((-1) * $intDays);
			$intHours = ((-1) * $intHours);
			$intMinutes = ((-1) * $intMinutes);
			$intSeconds = ((-1) * $intSeconds);
		}

		return array('Years' => $intYears, 'Months' => $intMonths, 'Days' => $intDays, 'Hours' => $intHours, 'Minutes' => $intMinutes,'Seconds' => $intSeconds);
	}

	/**
	 * Override method to perform a property "Get"
	 * This will get the value of $strName
	 *
	 * @param string $strName Name of the property to get
	 * @return mixed the returned property
	 */

	public function __get($strName) {
		switch ($strName) {
			case 'Years': return $this->GetYears();
			case 'Months': return $this->GetMonths();
			case 'Days': return $this->GetDays();
			case 'Hours': return $this->GetHours();
			case 'Minutes': return $this->GetMinutes();
			case 'Seconds': return $this->intSeconds;
			case 'Timearray' : return ($this->GetTimearray());

			default:
				try {
					return parent::__get($strName);
				} catch (AP_Exception $objExc) {
					throw $objExc;
				}
		}
	}

	/**
	 * Override method to perform a property "Set"
	 * This will set the property $strName to be $mixValue
	 *
	 * @param string $strName Name of the property to set
	 * @param string $mixValue New value of the property
	 * @return mixed the property that was set
	 */

	public function __set($strName, $mixValue) {
		try {
			switch ($strName) {
				case 'Seconds':
					return ($this->intSeconds = $mixValue);
				default:
					return (parent::__set($strName, $mixValue));
			}
		} catch (AP_Exception $objExc) {
			throw $objExc;
		}
	}
}