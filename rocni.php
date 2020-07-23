<?php
include 'params.php';
?>
<html lang="cz">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>  
    <script type="text/javascript">  
        google.charts.load('current', {'packages':['corechart']});
    </script>  
    <title>Jednoduchý účetní systém</title>
  </head>
  <body>
    <div class="container">
        <div class="row">
            <h1>Roční přehledy (<?php print $year; ?>)</h1>
        </div>
        <div class="row">
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">Roční bilance</th>
                        <th scope="col">Částka</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-success">
                        <th scope="row">Příjmy</th>
                        <td><?php print bilance_secist_rok($db, $year, "PRI"); ?> Kč</td>
                    </tr>
                    <tr class="table-danger">
                        <th scope="row">Výdaje</th>
                        <td><?php print bilance_secist_rok($db, $year, "VYD"); ?> Kč</td>
                    </tr>
                    <tr class="table-info">
                        <th scope="row">Celkem</th>
                        <td style="font-weight: bold; color: <?php print ($bilance_celk_rok < 0 ? 'red' : 'green') ?>"> <?php print $bilance_celk_rok; ?> Kč</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <script>  
            google.charts.setOnLoadCallback(drawCharts);
            function drawCharts() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Typ');
                data.addColumn('number', 'Částka');
                data.addRows([
                    ['Příjmy', <?php print bilance_secist_rok($db, $year, "PRI"); ?>],
                    ['Výdaje', <?php print bilance_secist_rok($db, $year, "VYD"); ?>],
                ]);
                var options = {
                    'title':'Roční příjmy a výdaje',
                    'width':400,
                    'height':400
                };
                var chart_bilance = new google.visualization.PieChart(document.getElementById('chart_bilance'));
                chart_bilance.draw(data,options);
            }
        </script>
        <div id="chart_bilance">
        </div>
    </div>
  </body>
</html>