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
  <div class="jumbotron jumbotron-fluid">
    <div class="container">
        <h1 class="display-4">Jednoduché účetnictví</h1>
        <p class="lead">Pro předmět A3PIS vytvořeno za pomoci PHP, JS, MySQL a Bootstrap 4. Grafy obstarává Google Charts API.</p>
    </div>
</div>
    <div class="container">
        <div class="row">
            <h1>Nová položka účetnictví</h1>
        </div>
        <div class="row">
            <form method="POST">
                <div class="form-group">
                    <label>Hodnota položky</label>
                    <input class="form-control" name="cena" type="number" placeholder="Hodnota položky v Kč" step="0.1" pattern="^\s*(?=.*[1-9])\d*(?:\.\d{1,2})?\s*$">
                </div>
                <div class="form-group">
                    <label>Kategorie položky</label>
                    <select name="kategorie" class="custom-select">
                    <option value="none">Vyberte kategorii</option>
                    <?php
                        $stmt = $db->prepare('SELECT * FROM kategorie ORDER BY nazev_kategorie ASC');
                        $stmt->execute();    

                        $result = $stmt->get_result();    
                        while ($row = $result->fetch_assoc()) {
                            print '
                                <option value="' . $row['ID'] . '">' . $row['nazev_kategorie'] . '</option>
                            ';
                        }
                    ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Poznámka</label>
                    <input type="text" class="form-control" name="poznamka" placeholder="Poznámka k položce" maxlength="40">
                </div>
                <div class="form-group">
                    <label>Typ položky</label>
                    <select name="typ" class="custom-select">
                        <option value="VYD">Výdaj</option>
                        <option value="PRI">Příjem</option>
                    </select>                
                </div>
                <div class="form-group">
                    <label>Datum</label>
                    <input type="date" class="form-control" name="datum" value="<?php print $today; ?>" max="<?php print $today; ?>"><br>
                    <input class="btn btn-primary" type="submit" name="submit" value="Přidat položku">               
                </div>

          </form>
            <?php 
                if ($_POST['submit']) {
                    $cena = $_POST['cena'];
                    $kat = $_POST['kategorie'];
                    $poz = $_POST['poznamka'];
                    $date = $_POST['datum'];
                    $typ = $_POST['typ'];
                    
                    if (empty($cena) || $kat == 'none' || empty($date)) {
                        print "Nastala chyba";
                    }
                    else {
                        $dateExp = explode('-', $date);
                        $y = $dateExp[0];
                        $m = $dateExp[1];
                        $d = $dateExp[2];
                        
                        $insert = $db->prepare('INSERT INTO items (ID, rok, mesic, den, cena, kategorie, typ, poznamka) VALUES (NULL,?,?,?,?,?,?,?)');
                        $insert->bind_param('iiidiss', $y, $m, $d, $cena, $kat, $typ, $poz);
                        $res = $insert->execute();
                    }
                }
            ?>
        </div>
        <div class="row">
            <a href="rocni.php" target="_blank"><h3>Zobrazit celkovou roční bilanci</h3></a>
        </div>
        <div class="row">
            <h1>Zobrazit měsíční evidenci</h1>
        </div>
        <div class="row">
            <form method="POST">
                <div class="form-row">             
                    <div class="col-auto">
                        <select name="month" class="custom-select">
                            <option value="none">Vybrat měsíc</option>
                            <?php
                            for($i=1; $i<=12; $i++) {
                                print '<option value="' . $i . '"' . ($i > $month ? "disabled" : "") . '>' . $i . '/' . $year . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="submit" class="btn btn-primary" name="tableHandler" value="Zobrazit">  
                    </div>
                </div>
            </form>
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">Datum</th>
                        <!--<th scope="col">Název položky</th>-->
                        <th scope="col">Cena</th>
                        <th scope="col">Kategorie / Typ</th>
                        <th scope="col">Poznámka</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        if ($_POST['tableHandler']) {
                            
                            $stmt = $db->prepare('SELECT * FROM items LEFT JOIN `kategorie` on `items`.`kategorie`=`kategorie`.`ID` WHERE `mesic` = ? ORDER BY den ASC');
                            $stmt->bind_param('i', $_POST['month']);
                            $stmt->execute();
    
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                print 
                                    '<tr class="' . ($row['typ'] == 'VYD' ? 'table-danger' : 'table-success') . '">
                                        <th scope="row">'. $row['den'] . '.' . $row['mesic'] . '.' . $row['rok'] .'</th>
                                        <td>'. $row['cena'] .' Kč </td>
                                        <td>'. $row['nazev_kategorie'] . ' (' . $row['typ'] . ') </td>
                                        <td>'. $row['poznamka'] .'</td>
                                    </tr>
                                    ';
                            }
                        
                    ?>
                </tbody>
            </table>
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">Měsíční bilance</th>
                        <th scope="col">Částka</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-success">
                        <th scope="row">Příjmy</th>
                        <td> <?php print $bilance_prijmy; ?> Kč</td>
                    </tr>
                    <tr class="table-danger">
                        <th scope="row">Výdaje</th>
                        <td> <?php print $bilance_vydaje; ?> Kč</td>
                    </tr>
                    <tr class="table-info">
                        <th scope="row">Celková bilance</th>
                        <td style="font-weight: bold; color: <?php print ($bilance_celk_mesic < 0 ? 'red' : 'green') ?>"> <?php print $bilance_celk_mesic; ?> Kč</td>
                    </tr>
                </tbody>
            </table>
                        <script>  
                            google.charts.setOnLoadCallback(drawCharts);
                            function drawCharts() {
                                var data = new google.visualization.DataTable();
                                data.addColumn('string', 'Typ');
                                data.addColumn('number', 'Částka');
                                data.addRows([
                                  ['Příjmy', <?php print $bilance_prijmy; ?>],
                                  ['Výdaje', <?php print $bilance_vydaje; ?>],
                                ]);
                                var data2 = google.visualization.arrayToDataTable([
                                    ["Den", "Částka"],
                                    <?php
                                        $stmt = $db->prepare('SELECT typ, mesic, den, SUM(cena) as bilance FROM items WHERE mesic = ? AND typ = "VYD" GROUP BY den');
                                        $stmt->bind_param('i', $_POST['month']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        while ($row = $result->fetch_assoc()) {
                                            for ($i=1; $i<=31; $i++) {
                                                print '["' . $i . '.' . $_POST['month'] . '", ' . ($i == $row['den'] ? ($row['bilance']) : 0) . '],';
                                            }
                                            //print '["' . intval($row['den']) . '", "' . intval($row['bilance']) .'"],';
                                        }
                                    ?>
                                ]);
                                var data3 = google.visualization.arrayToDataTable([
                                    ["Den", "Částka"],
                                    <?php
                                        $stmt = $db->prepare('SELECT typ, mesic, den, SUM(cena) as bilance FROM items WHERE mesic = ? AND typ = "PRI" GROUP BY den');
                                        $stmt->bind_param('i', $_POST['month']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        while ($row = $result->fetch_assoc()) {
                                            for ($i=1; $i<=31; $i++) {
                                                print '["' . $i . '.' . $_POST['month'] . '", ' . ($i == $row['den'] ? ($row['bilance']) : 0) . '],';
                                            }
                                            //print '["' . intval($row['den']) . '", "' . intval($row['bilance']) .'"],';
                                        }
                                    ?>
                                ]);
                                var options = {'title':'Graf příjmů a výdajů',
                                'width':400,
                                'height':400
                                };
                                var options2 = {'title':'Přehled výdajů za dny',
                                'width':1200,
                                'height':400
                                };
                                var options3 = {'title':'Přehled příjmů za dny',
                                'width':1200,
                                'height':400
                                };
                                var chart_bilance = new google.visualization.PieChart(document.getElementById('chart_bilance'));
                                var chart_denni = new google.visualization.ColumnChart(document.getElementById('chart_denni'));
                                var chart_denni_prijmy = new google.visualization.ColumnChart(document.getElementById('chart_denni_prijmy'));
                                chart_bilance.draw(data,options);
                                chart_denni.draw(data2,options2);
                                chart_denni_prijmy.draw(data3,options3);
                            }
                        </script>
                        <div id="chart_bilance">
                        </div><br>
                        <div id="chart_denni">
                        </div>
                        <div id="chart_denni_prijmy">
                        </div>
        <?php       
        }
        ?>
        </div>
    </div>
  </body>
</html>