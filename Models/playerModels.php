<?php
namespace Models;

class playerModels {


    /**
     * Execute a query & return the resulting data as an array of assoc arrays
     * @param object $search, boolean searchPlayerStat
     * @return sql query
     * otherwise true or false for insert/update/delete success
     */

    function getPlayers($search, $searchPlayerStat = false) {
        $where = [];
        if ($search->has('playerId')) {
            $where[] = "roster.id = '" . $search['playerId'] . "'";
        }
        if ($search->has('player')) {
            $where[] = "roster.name = '" . $search['player'] . "'";
        }
        if ($search->has('team')) {
            $where[] = "roster.team_code = '" . $search['team']. "'";
        }
        if ($search->has('position')) {
            $where[] = "roster.pos = '" . $search['position'] . "'";
        }
        if ($search->has('country')) {
            $where[] = "roster.nationality = '" . $search['country'] . "'";
        }
        $where = implode(' AND ', $where);

        // if searchPlayerStat == true
        if ($searchPlayerStat) {
            $sql = "
                SELECT roster.name, player_totals.*
                FROM player_totals 
                    INNER JOIN roster ON (roster.id = player_totals.player_id)";
        } else {
            $sql = "
                SELECT roster.*
                FROM roster ";
        }
        $sql .= "WHERE $where";
        return query($sql) ?: [];
    }
}
?>