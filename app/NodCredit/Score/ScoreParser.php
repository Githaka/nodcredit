<?php

namespace App\NodCredit\Score;

use App\ScoreConfig;

/**
 * Class ScoreParser
 *
 * The job of this class is to read the score config and parse it
 *
 * @package \App\NodCredit\Score
 */
class ScoreParser {


    private $score;

    private $info;

    /**
     * This variable hold count like days or how often something has happened.
     * When passed it, it can be used to select the right score
     *
     * @var null
     */
    private $eventOccurrenceCount;

    /**
     * ScoreParser constructor.
     *
     * @param $key
     * @param null $eventOccurrenceCount
     */
    public function __construct($key, $eventOccurrenceCount=null)
    {
        //TODO: extract to scope
       $this->score =  ScoreConfig::where('name', $key)->first();
       $this->eventOccurrenceCount = $eventOccurrenceCount;

    }

    /**
     * The parser take account 3 different cases to parse the score config
     * /TODO: split the method
     *
     * @throws \App\NodCredit\Score\BadScoreConfigException
     */
    private function parse()
    {
        if(!$this->score) throw new BadScoreConfigException('Event is not valid');

        // if no frequencies, we just return the score and the name
        if(!$this->score->frequencies)
        {
            $this->info = ['score' => $this->score->score, 'id' => $this->score->id, 'name' => $this->score->name];
        }
        else
        {
            // we have additional 2 possibilities here.
            if(!$this->score->repeatable && $this->eventOccurrenceCount !== null)
            {
                foreach($this->score->frequencies as $frequency)
                {
                    if(isset($frequency['between']) && $this->getBetween($this->eventOccurrenceCount, $frequency['between']))
                    {
                        $this->info = ['score' => $frequency['score'], 'id' => $this->score->id, 'name' => $this->score->name];
                        //break;
                    }
                }
            }
            else
            {
                // let`s handle the score that can be applied to repeated events
                foreach($this->score->frequencies as $frequency)
                {
                    if(isset($frequency['amount']) && $this->eventOccurrenceCount !== null)
                    {

                        if($this->eventOccurrenceCount == $frequency['amount'] && !isset($frequency['repeat']))
                        {
                            $this->info = ['score' => $frequency['score'], 'id' => $this->score->id, 'name' => $this->score->name];
                        }
                        else
                        {
                            if($this->eventOccurrenceCount % $frequency['amount'] === 0 && isset($frequency['repeat']))
                            {
                                $this->info = ['score' => $frequency['score'], 'id' => $this->score->id, 'name' => $this->score->name];
                            }
                        }
                    }
                }
            }

        }
    }

    /**
     * Get the config that matches the supplied range.
     * Example: 2, '1-2' will match the config that is between 1 to 2 days
     * @param $n
     * @param $input
     *
     * @return bool
     */
    private function getBetween($n, $input) {
        $parts  = explode('-', $input);
        if(count($parts) !== 2) {
            return false;
        }
        if($n >= $parts[0] && $n <= $parts[1])  {
            return true;
        }
        return false;
    }

    public function getInfo()
    {
        $this->parse();
        return $this->info ? (object)$this->info : false;
    }

}
