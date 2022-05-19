<?php
use Illuminate\Support;  // https://laravel.com/docs/5.8/collections - provides the collect methods & collections class
use LSS\Array2Xml;


require_once('Controllers/Exporter.php');
require_once('Models/playerModels.php');
require_once('include/utils.php');


class Controller {

    public function __construct($args) {
        $this->args = $args;
    }

    /**
     * Execute a query & return the resulting data as an array of assoc arrays
     * @param string $sql query to execute
     * @return csv, json, xml
     * otherwise true or false for insert/update/delete success
     */
    public function export($type, $format) {
        $view = [];
        $exporter = new Exporter();
        switch ($type) {
            case 'playerstats':
                $searchArgs = ['player', 'playerId', 'team', 'position', 'country'];
                $search = $this->args->filter(function($value, $key) use ($searchArgs) {
                    return in_array($key, $searchArgs);
                });
                $view = $exporter->getPlayerStats($search);
                break;
            case 'players':
                $searchArgs = ['player', 'playerId', 'team', 'position', 'country'];
                $search = $this->args->filter(function($value, $key) use ($searchArgs) {
                    return in_array($key, $searchArgs);
                });
                $view = $exporter->getPlayers($search);
                break;
        }
        if (!$view) {
            exit("Error: No data found!");
        }
        if (strcmp('html', $format) == 0) {
            $view = [
                'fileDir' => 'table',
                "vars" => [
                    "data" => $view->toArray(),
                    'headings' => []
                ] 
            ];
        }
        return $exporter->format($view, $format);
    }
}