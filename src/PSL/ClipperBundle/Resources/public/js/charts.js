


// TODO: remove object from this file, dev demo only
var chartsdata = [

  //////////
  {
    // required
    chartmachinename: "net_promoters",
    // required
    charttype: "BarChart",
    // optional
    drilldown: { 
      countries: ['USA', 'Canada'],
      specialties: ['Oncology', 'Diabetes'],
      regions: ['Europe'],
    },
    // required 
    datatable: {"cols":[{"id":"b","label":"Brand","type":"string"},{"id":"P","label":"Promoters","type":"number"},{"id":"a","label":"Passives","type":"number"},{"id":"d","label":"Detractors","type":"number"},{"id":"s","label":"Score","type":"number"}],"rows":[{"c":[{"v":"AA-123"},{"v":0,"f":"0%"},{"v":0,"f":"0%"},{"v":100,"f":"100%"},{"v":-100,"f":"-100"}],"p":{"Brand":"AA-123"}},{"c":[{"v":"BB-456"},{"v":50,"f":"50%"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":0,"f":"0"}],"p":{"Brand":"BB-456"}},{"c":[{"v":"CC-789"},{"v":0,"f":"0%"},{"v":0,"f":"0%"},{"v":100,"f":"100%"},{"v":-100,"f":"-100"}],"p":{"Brand":"CC-789"}},{"c":[{"v":"DD-123"},{"v":50,"f":"50%"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":0,"f":"0"}],"p":{"Brand":"DD-123"}},{"c":[{"v":"EE-456"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":50,"f":"50%"},{"v":-50,"f":"-50"}],"p":{"Brand":"EE-456"}},{"c":[{"v":"FF-789"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":50,"f":"50%"},{"v":-50,"f":"-50"}],"p":{"Brand":"FF-789"}}]}, 
  }
  
  ////////////
  ,{
    charttype: "BarChart", 
    datatable: {"cols":[{"id":"b","label":"Brand","type":"string"},{"id":"P","label":"Promoters","type":"number"},{"id":"a","label":"Passives","type":"number"},{"id":"d","label":"Detractors","type":"number"},{"id":"s","label":"Score","type":"number"}],"rows":[{"c":[{"v":"AA-123"},{"v":0,"f":"0%"},{"v":0,"f":"0%"},{"v":100,"f":"100%"},{"v":-100,"f":"-100"}],"p":{"Brand":"AA-123"}},{"c":[{"v":"BB-456"},{"v":50,"f":"50%"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":0,"f":"0"}],"p":{"Brand":"BB-456"}},{"c":[{"v":"CC-789"},{"v":0,"f":"0%"},{"v":0,"f":"0%"},{"v":100,"f":"100%"},{"v":-100,"f":"-100"}],"p":{"Brand":"CC-789"}},{"c":[{"v":"DD-123"},{"v":50,"f":"50%"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":0,"f":"0"}],"p":{"Brand":"DD-123"}},{"c":[{"v":"EE-456"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":50,"f":"50%"},{"v":-50,"f":"-50"}],"p":{"Brand":"EE-456"}},{"c":[{"v":"FF-789"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":50,"f":"50%"},{"v":-50,"f":"-50"}],"p":{"Brand":"FF-789"}}]}, 
  }
];



/**
 * logic starts here
 */


/**
 *  ChartForm
 */
var ChartForm = React.createClass({
  
  handleSubmit: function(e) {
    e.preventDefault();
    var country = React.findDOMNode(this.refs.country).value.trim();
    var specialty = React.findDOMNode(this.refs.specialty).value.trim();
    var region = React.findDOMNode(this.refs.region).value.trim();
    this.props.loadCharts({
      chartmachinename: this.props.chartmachinename, 
      country: country, 
      specialty: specialty,
      region: region,
    });
  },
  
  Countries: function(){
    var output = this.props.countries.map(function(country, index){
      return (
        <option key={index} value={country}>{country}</option>        
      );
    });
    return (
      <select ref="country">
        {output}
      </select>
    );
  },
  
  Specialties: function(){
    var output = this.props.specialties.map(function(specialty, index){
      return (
        <option key={index} value={specialty}>{specialty}</option>        
      );
    });
    return (
      <select ref="specialty">
        {output}
      </select>
    );
  },
  
  Regions: function(){
    var output = this.props.regions.map(function(region, index){
      return (
        <option key={index} value={region}>{region}</option>        
      );
    });
    return (
      <select ref="region">
        {output}
      </select>
    );
  },
  
  render: function() {
    return (
      <form className="chart-form" onSubmit={this.handleSubmit}>
        {this.Countries()}
        {this.Regions()}
        {this.Specialties()}
        <input type="submit" value="Filter" />
      </form>
    );
  }
})


/**
 * GoogleChart
 */
var GoogleChart = React.createClass({
  
  drawChart: function(){
    // TODO: integrate overlays
    
    var data = new google.visualization.DataTable(this.props.datatable);
    var chart = new google.visualization[this.props.charttype](
      React.findDOMNode(this)
    );
    
    // TODO: remove hardcoded options object
    var options = {
      isStacked: 'percent',
      legend : {
        position : 'top',
        maxLines : 3
      },
    };
    
    chart.draw(data, options);
  },
  componentDidMount: function(){
    google.setOnLoadCallback(this.drawChart());
  },
  componentDidUpdate: function(){
    this.drawChart();
  },
  render: function(){
    return (
      <div 
        className="google-chart" 
        style={{ height: "500px", width: "500px"}} 
      />
    );
  },
  
});


/**
 * Chart wrapper
 */
var Chart = React.createClass({
  
  hasForm: function() {
    return this.props.countries.length && this.props.regions.length && this.props.specialties.length;
  },
  
  render: function() {
    return (
      <div className="chart-container">
        <GoogleChart 
          chartmachinename = {this.props.chartmachinename}
          charttype = {this.props.charttype}
          datatable = {this.props.datatable}
        />  
        
        {  this.hasForm() ? 
          <ChartForm 
            chartmachinename={this.props.chartmachinename} 
            countries={this.props.countries} 
            specialties={this.props.specialties}
            regions={this.props.regions}
            loadCharts={this.props.loadCharts}
          /> : null }
      </div>
    );
  }
  
});


/**
 * ChartList
 */
var ChartList = React.createClass({
  
  loadCharts: function(drilldown) {
    this._getChartsData(drilldown);
  },
  
  getInitialState: function() {
    return {data: []};
  },
  
  componentDidMount: function() {
    this._getChartsData();
  },
  
  _getChartsData: function(request){
    var $element = $('#react-content');
    var url = $element.data('charts_data_url');
    
    // post
    var request = request || {};
    request.order_id = $element.data('order_id');
    
    $.ajax({
      url: url,
      dataType: 'json',
      type: 'POST',
      data: request,
      success: function(data) {
        switch(data.status){
          case 200:
            this.setState({data: data.content || []});
          break;
          default:
            console.error(url, data.status, data.content.toString());
          break;
        }
      }.bind(this),
      error: function(xhr, status, err) {
        console.error(url, status, err.toString());
      }.bind(this)
    });
    
  },
  
  render: function() {
    var loadCharts = this.loadCharts;
    var chartNodes = this.state.data.map(function(chart, index) {
      var drill = chart.drilldown || {};
      return (
        <Chart 
          key={index} 
          chartmachinename={chart.chartmachinename} 
          charttype={chart.charttype} 
          datatable={chart.datatable || {}} 
          countries={drill.countries || []}
          specialties={drill.specialties || []}
          regions={drill.regions || []}
          loadCharts={loadCharts}
        />
      );  
    });
    
    return (
      <div className="charts-list">
        {chartNodes}
      </div>
    );
  }
  
});


/**
 * output to page
 */

React.render(
  <ChartList />
  ,document.getElementById('react-content')
);


