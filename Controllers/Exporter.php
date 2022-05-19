<?php
use Illuminate\Support;
use LSS\Array2Xml;
use Models\playerModels;

// retrieves & formats data from the database for export
class Exporter {
    private $model;
    public function __construct() {
        $this->model = new playerModels();
    }

    /**
     * Get Players Stats
     * @param $search array
     * @return object
     * 
     */
    function getPlayerStats($search) {
        $data = $this->model->getPlayers($search, true);
        
        foreach ($data as &$row) {
            unset($row['player_id']);
            $row['total_points'] = ($row['3pt'] * 3) + ($row['2pt'] * 2) + $row['free_throws'];
            $row['field_goals_pct'] = $row['field_goals_attempted'] ? (round($row['field_goals'] / $row['field_goals_attempted'], 2) * 100) . '%' : 0;
            $row['3pt_pct'] = $row['3pt_attempted'] ? (round($row['3pt'] / $row['3pt_attempted'], 2) * 100) . '%' : 0;
            $row['2pt_pct'] = $row['2pt_attempted'] ? (round($row['2pt'] / $row['2pt_attempted'], 2) * 100) . '%' : 0;
            $row['free_throws_pct'] = $row['free_throws_attempted'] ? (round($row['free_throws'] / $row['free_throws_attempted'], 2) * 100) . '%' : 0;
            $row['total_rebounds'] = $row['offensive_rebounds'] + $row['defensive_rebounds'];
        }
        return collect($data);
    }

    /**
     * Get Players
     * @param $search array
     * @return object
     * 
     */
    function getPlayers($search) {
        $data = $this->model->getPlayers($search);
        return collect($data)
            ->map(function($item, $key) {
                unset($item['id']);
                return $item;
            });
    }

    /**
     * Menu to determine what file type is requested by the user
     * @param object data string format
     * @return designated file format
     * 
     */
    public function format($data, $format = 'html') {

        if ($format=="html"){
            $this->view($data['fileDir'],$data['vars']);
          }elseif($format=="xml"){
            return  $this->xml($data);
          }elseif($format=="csv"){
             return $this->csv($data);
          }else{
              //json
             return $this->json($data);
          }
    }

    /**
     * Menu to determine what file type is requested by the user
     * @param object data string format
     * @return json
     * 
     */
    public function json($data) {
        header('Content-type: application/json');
        return json_encode($data->all());
    }

    /**
     * Create a XML file 
     * @param object data
     * @return xml
     * 
     */
    public function xml($data) {
        header('Content-type: text/xml');

        // fix any keys starting with numbers
        $keyMap = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
        $xmlData = [];
        foreach ($data->all() as $row) {
            $xmlRow = [];
            foreach ($row as $key => $value) {
                $key = preg_replace_callback('(\d)', function($matches) use ($keyMap) {
                    return $keyMap[$matches[0]] . '_';
                }, $key);
                $xmlRow[$key] = $value;
            }
            $xmlData[] = $xmlRow;
        }
        $xml = Array2XML::createXML('data', [
        'entry' => $xmlData
        ]);
        return $xml->saveXML();
    }

    public function view($fileDir,$vars = null) {
      if(!empty($vars)){
        extract($vars);
      }
      try {
        include("views".DIRECTORY_SEPARATOR.$fileDir.".php");
      } catch (\Exception $e) {
        throw new \Exception("Unknown file '$fileDir'");      
      }
    }

    /**
     * Create a CSV file 
     * @param object data
     * @return csv
     * 
     */
    public function csv($data) {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="export.csv";');
        if (!$data->count()) {
            return;
        }
        $csv = [];

        // extract headings
        // replace underscores with space & ucfirst each word for a decent headings
        $headings = collect($data->get(0))->keys();
        $headings = $headings->map(function($item, $key) {
            return collect(explode('_', $item))
                ->map(function($item, $key) {
                    return ucfirst($item);
                })
                ->join(' ');
        });
        $csv[] = $headings->join(',');
        // format data
        foreach ($data as $dataRow) {
            $csv[] = implode(',', array_values($dataRow));
        }
        return implode("\n", $csv);
    }

}

?>