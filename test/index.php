<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>

            <title>Projet GreenFeed - Station de recharge a energie positive</title>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />


        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script type="text/javascript">

        // Load the Visualization API and the piechart package.
        google.load('visualization', '1', {'packages':['corechart']});

        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart);
		var num=1;
        function drawChart() {
		num=num+100;
//AJAX Call is compulsory !

        var jsonData = $.ajax({
          url: "fetch.php?c="+num,
          dataType:"json",
          async: false
          }).responseText;

          // Create our data table out of JSON data loaded from server.
        var data = new google.visualization.DataTable(jsonData);

		var optionsClassPie = {
			title: 'Total Hourly Emails by Class',
			height: 450,
			chartArea: { left: 50, width: '100%' },
            legend: { position: 'top', maxLines: 10 },
			bar: { groupWidth: '75%' },
			hAxis: { slantedText: 'true' },
			isStacked: true,
			sliceVisibilityThreshold: 1
		};
          // Instantiate and draw our chart, passing in some options.
          // Do not forget to check your div ID
		  var classPie = new google.visualization.ColumnChart(document.getElementById("chart_div"));
          classPie.draw(data, optionsClassPie);	
        }		
        </script>


        <script type="text/javascript" src="jquery-2.1.3.min.js"></script>
        <script type="text/javascript">

                $(document).ready(function(){
                    // First load the chart once 
                    drawChart();
                    // Set interval to call the drawChart again
                    setInterval(drawChart, 100);
                    });
        </script>

    </head>

    <body>

        <div id="chart_div" style="width:99%;height:99%;clear:left;"> </div>

    </body>
</html>