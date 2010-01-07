<?php
class Helper_HoursAsDays
{
	/**
	 * Get the amount of elapsed time for a given number of seconds.
	 *
	 * @return  NovemberUser
	 */
	public function HoursAsDays($hours,$dayDivision=0,$dayLength=0,$divisionTolerance=0)
	{
		if ($hours > 0) {
			$divisionTolerance = $divisionTolerance ? $divisionTolerance :  za()->getConfig('division_tolerance', 20);
			$dayLength = $dayLength ? $dayLength : za()->getConfig('day_length', 7.5);
			$dayDivision = $dayDivision ? $dayDivision : $dayLength / 4;
            $dayPercentage = $dayDivision / $dayLength;
            $hoursAsDays = floor ($hours / $dayDivision) * $dayPercentage + $dayPercentage;

            // if the time was only a little above a day division, lets be nice to our
            // clients and drop off that quarter day charge
            $overDraft = fmod($hours, $dayDivision);
            
            if ($overDraft < ($dayDivision * ($divisionTolerance/100))) {
                $hoursAsDays -= $dayPercentage;
            }
            return $hoursAsDays;
		}
	}
}
?>
