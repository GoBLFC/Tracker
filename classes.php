<?php

class UserTimeClock {

    // Tracker completely ignores timezones currently.
    private static $MYSQL_FORMAT = "Y-m-d H:i:s";

    private static function calculateOverlap($timeBegin1, $timeEnd1, $timeBegin2, $timeEnd2) {
        // Returns the overlap of two time frames in seconds.
        // Arguments should implement DateTimeInterface.
        // In other words, they should be DateTime or DateTimeImmutable objects.

        // Take the larger (more recent) starting time.
        $overlapBegin = ($timeBegin1 < $timeBegin2) ? $timeBegin2 : $timeBegin1;
        // Take the smaller (less recent) ending time.
        $overlapEnd = ($timeEnd1 < $timeEnd2) ? $timeEnd1 : $timeEnd2;

        $overlap = $overlapEnd->getTimestamp() - $overlapBegin->getTimestamp();

        // Negative number means no overlap, so return 0
        if ($overlap < 0) { return 0; }

        return $overlap;
    }

    public static function calculateBonuses($times, $bonuses) {
        // Calculate how much bonus time to reward (in seconds) for a list of time frames.
        // Supports stacking bonuses and also takes into account which departments they apply to.

        $earnedBonuses = [];

        foreach ($times as $time) {
            $timeStart = DateTime::createFromFormat(self::$MYSQL_FORMAT, $time["check_in"]);
            // If this is an ongoing shift, take the current time.
            if (!isset($time["check_out"])) {
                $timeStop = new DateTime();
            } else {
                $timeStop = DateTime::createFromFormat(self::$MYSQL_FORMAT, $time["check_out"]);
            }

            $bonusTotal = 0;

            foreach ($bonuses as $bonus) {
                // Skip the bonus if the department does not apply.
                $departments = explode(",", $bonus["dept"]);
                if (!in_array($time["dept"], $departments)) {
                    continue;
                }

                $bonusStart = DateTime::createFromFormat(self::$MYSQL_FORMAT, $bonus["start"]);
                $bonusStop = DateTime::createFromFormat(self::$MYSQL_FORMAT, $bonus["stop"]);

                $bonusTime = self::calculateOverlap($timeStart, $timeStop, $bonusStart, $bonusStop);
                // Apply modifier, and ensure we don't include the original time contributed in the bonus.
                $bonusTime = (int) round($bonusTime * $bonus["modifier"]) - $bonusTime;

                $bonusTotal += $bonusTime;
            }

            $earnedBonuses[$time["id"]] = $bonusTotal;
        }

        return $earnedBonuses;
    }

    public static function calculateTimeSinceDay($times, $date) {
        // Given a list of times, returns the number of seconds contributed since $date at midnight.
        // Times starting before $date at 12:00 AM will have their time counted from that point on only.
        // Times without a stop point will be assumed to be ongoing and be set to the current time.

        $date->setTime(0, 0, 0, 0); // Set time to midnight

        $timeTotal = 0;

        foreach ($times as $time) {
            $timeStart = DateTime::createFromFormat(self::$MYSQL_FORMAT, $time["check_in"]);
            // If this is an ongoing shift, take the current time.
            if (!isset($time["check_out"])) {
                $timeStop = new DateTime();
            } else {
                $timeStop = DateTime::createFromFormat(self::$MYSQL_FORMAT, $time["check_out"]);
            }

            // Time stops before threshold date, discard it
            if ($timeStop < $date) { continue; }
            // If time starts before threshold time, move it forward
            if ($timeStart < $date) { $timeStart = $date; }

            $timeTotal += $timeStop->getTimestamp() - $timeStart->getTimestamp();
        }

        return $timeTotal;
    }

    public static function calculateTimeTotal($times, $bonuses = null) {
        // Given a list of times, calculate how much time total has been contributed in seconds.
        // Times without a check-out time will be assumed to be ongoing, and current time will be used.
        // If an array of bonuses is also provided, these will be calculated and added to the total time.

        $timeTotal = 0;

        foreach ($times as $time) {
            $timeStart = DateTime::createFromFormat(self::$MYSQL_FORMAT, $time["check_in"]);
            // If this is an ongoing shift, take the current time.
            if (!isset($time["check_out"])) {
                $timeStop = new DateTime();
            } else {
                $timeStop = DateTime::createFromFormat(self::$MYSQL_FORMAT, $time["check_out"]);
            }

            $timeTotal += $timeStop->getTimestamp() - $timeStart->getTimestamp();
        }

        if ($bonuses) {
            $earnedBonuses = self::calculateBonuses($times, $bonuses);
            foreach (array_values($earnedBonuses) as $bonus) {
                $timeTotal += $bonus;
            }
        }

        return $timeTotal;
    }

    public static function determineEligibleRewards($times, $rewards, $bonuses = null, $claims = null) {
        // Given a list of times, determine what rewards a volunteer would be eligible to claim.
        // The rewards parameter must be provided. If bonuses are provided, they will be calculated and
        // added to the volunteer's hours before determining eligibility.

        // Each reward will have an "available" key that will be true if the volunteer can claim it, and
        // false otherwise. If the claims parameter is provided, each reward will additionally have a "claimed"
        // key that will be true if the reward was already claimed, and false otherwise.

        $timeTotal = self::calculateTimeTotal($times, $bonuses) / 60 / 60; // Seconds to hours

        if ($claims) {
            $claimed = [];
            foreach ($claims as $claim) {
                $claimed[] = $claim["claim"];
            }
        }

        foreach ($rewards as &$reward) {
            if ($timeTotal >= $reward["hours"]) {
                $reward["available"] = true;
            } else {
                $reward["available"] = false;
            }

            if ($claims) {
                if (in_array($reward["id"], $claimed)) {
                    $reward["claimed"] = true;
                } else {
                    $reward["claimed"] = false;
                }
            }
        }

        return $rewards;
    }

}

?>
