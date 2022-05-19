<html>
    <head>
        <link rel="stylesheet" href="static/report.css" type="text/css">
    </head>
    <body>
        <?php

            /**
             * Use this file to output reports required for the SQL Query Design test.
             * An example is provided below. You can use <the></the> `asTable` method to pass your query result to,
             * to output it as a styled HTML table.
             */

            $database = 'nba2019';
            require_once('vendor/autoload.php');
            require_once('include/utils.php');

            /*
             * Example Query
             * -------------
             * Retrieve all team codes & names
             */
            echo '<h1>Example Query</h1>';
            $teamSql = "SELECT * FROM team";
            $teamResult = query($teamSql);
            // dd($teamResult);
            echo asTable($teamResult);

            /*
             * Report 1
             * --------
             * Produce a query that reports on the best 3pt shooters in the database that are older than 30 years old. Only 
             * retrieve data for players who have shot 3-pointers at greater accuracy than 35%.
             * 
             * Retrieve
             *  - Player name
             *  - Full team name
             *  - Age
             *  - Player number
             *  - Position
             *  - 3-pointers made %
             *  - Number of 3-pointers made 
             *
             * Rank the data by the players with the best % accuracy first.
             */
            echo '<h1>Report 1 - Best 3pt Shooters</h1>';
            $teamSql = "
            SELECT  
                roster.name as `Player name`, 
                team.name as `Team name`, 
                (year(CURRENT_TIMESTAMP)-year(roster.dob)) as `Age` ,
                roster.number as `Player number`, 
                roster.pos as `Position`, 
                player_totals.3pt as `3-pointers made %`, 
                ((player_totals.3pt /player_totals.3pt_attempted)*100) as `Number of 3-pointers made`
            FROM 
                player_totals
                    INNER JOIN 
                roster ON roster.id = player_totals.player_id
                    INNER JOIN 
                team ON roster.team_code = team.code
                Where ((player_totals.3pt /player_totals.3pt_attempted)*100) > 35 AND (year(CURRENT_TIMESTAMP)-year(roster.dob))>30
                ORDER BY ((player_totals.3pt /player_totals.3pt_attempted)*100) DESC";
            $teamResult = query($teamSql);
            echo asTable($teamResult);
            // write your query here


            /*
             * Report 2
             * --------
             * Produce a query that reports on the best 3pt shooting teams. Retrieve all teams in the database and list:
             *  - Team name
             *  - 3-pointer accuracy (as 2 decimal place percentage - e.g. 33.53%) for the team as a whole,
             *  - Total 3-pointers made by the team
             *  - # of contributing players - players that scored at least 1 x 3-pointer
             *  - of attempting player - players that attempted at least 1 x 3-point shot
             *  - total # of 3-point attempts made by players who failed to make a single 3-point shot.
             * 
             * You should be able to retrieve all data in a single query, without subqueries.
             * Put the most accurate 3pt teams first.
             */
            echo '<h1>Report 2 - Best 3pt Shooting Teams</h1>';
            $teamSql = "
            SELECT 
                team.name AS `Team name`,
                CONCAT(FORMAT(SUM(player_totals.3pt) / SUM(player_totals.3pt_attempted) * 100, 2), '%') AS `pecentage`,
                SUM(player_totals.3pt) AS `Total 3-pointers made by the team`,
                SUM(case when player_totals.3pt >= 1 then 1 else 0 end) AS `# of contributing players`,
                SUM(case when player_totals.3pt_attempted >= 1 then 1 else 0 end) AS `# of attempting players`,
                SUM(case when (player_totals.3pt_attempted >= 1 and player_totals.3pt = 0) then player_totals.3pt_attempted else 0 end) AS `total # of 3-point attempts made by players who failed to make a single 3-point shot.`
            FROM
                team
                    INNER JOIN
                roster ON roster.team_code = team.code
                    INNER JOIN
                player_totals ON roster.team_code = team.code
                Where roster.id = player_totals.player_id 
                group by team.name
                ORDER BY SUM(player_totals.3pt) / SUM(player_totals.3pt_attempted) * 100 DESC
            ";
            $teamResult = query($teamSql);
            echo asTable($teamResult);
        ?>
    </body>
</html>