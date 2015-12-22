/* charts.pdf.custom.js */

var chartsconfig = ChartsSettings, // config objec from charts.pdf.config.js
    clippercharts = {}, // clippercharts object from clipper.charts.js
    charttype = 'bars', // from CDATA charttype in html
    chartid = 'detractors', // from CDATA chartid in html
    datatable = {}; // from CDATA datatable in html
    

var GoogleChart = {
  drawChart: function() {
    try {
      var chart = clippercharts.factory(charttype, chartid, {
        formatter: charttype
      }, datatable);
    } catch(e) {
      console.log('Error:' + e);
    }
  }
};

GoogleChart.drawChart();