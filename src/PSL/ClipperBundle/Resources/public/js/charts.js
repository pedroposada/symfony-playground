
/**
 * logic starts here
 */

/**
 *  Loader
 */
var Loader = React.createClass({
  render: function(){
    return (
      <div className="sk-cube-grid" style={this.props.style}>
        <div className="sk-cube sk-cube1"></div>
        <div className="sk-cube sk-cube2"></div>
        <div className="sk-cube sk-cube3"></div>
        <div className="sk-cube sk-cube4"></div>
        <div className="sk-cube sk-cube5"></div>
        <div className="sk-cube sk-cube6"></div>
        <div className="sk-cube sk-cube7"></div>
        <div className="sk-cube sk-cube8"></div>
        <div className="sk-cube sk-cube9"></div>
      </div>
    );
  }
});


/**
 *  ChartForm
 */
var ChartForm = React.createClass({  
  _handleChange: function(e) {
    e.preventDefault();
    
    var region = this.refs.region ? React.findDOMNode(this.refs.region).value : '';
    var country = this.refs.country ? React.findDOMNode(this.refs.country).value : '';
    var specialty = this.refs.specialty ? React.findDOMNode(this.refs.specialty).value : '';
    
    this.props.loadCharts({
      chartmachinename: this.props.chartmachinename, 
      country: country, 
      specialty: specialty,
      region: region,
    });
  },
  render: function() {
    return (
      <form className="chart-form" onChange={this._handleChange}>
        { this.props.regions.length > 1 ?
          <ChartFilter
            items         = {this.props.regions} 
            ref           = "region" 
            text          = 'All regions' 
          /> 
        : null }
        { this.props.countries.length > 1 ?
          <ChartFilter
            items         = {this.props.countries} 
            ref           = "country" 
            text          = 'All countries' 
          /> 
        : null }
        { this.props.specialties.length > 1 ?
          <ChartFilter
            items         = {this.props.specialties }
            ref           = "specialty" 
            text          = 'All specialties' 
          /> 
        : null }
      </form>
    );
  }
});

/**
 * Filters
 */
var ChartFilter = React.createClass({
  render: function() {
    var children = this.props.items.map(function(item, index){
      return (
        <option key={index} value={item}>{item}</option>
      );
    });
    return (
        <select 
          ref={this.props.ref}
        >
          <option key="" value="">{this.props.text}</option>
          {children}
        </select>
    );
  }
});

/**
 * GoogleChart
 */
var GoogleChart = React.createClass({ 
  drawChart: function() {
    try {
      var chart = clipper.charts.factory(this.props.charttype, this.props.id, {
        formatter: this.props.charttype
      }, this.props.datatable);
    } catch(e) {
      console.log('Error:' + e);
    }
  },
  componentDidMount: function(){
    google.setOnLoadCallback(this.drawChart());
  },
  componentDidUpdate: function(){
    this.drawChart();
  },
  render: function(){
    var classString = 'google-chart';
    if(this.props.isLoadingChart) {
      classString += ' hidden';
    }
    return (
      <div 
        className = {classString} 
        id = {this.props.id}
        style = {this.props.style}
      />
    );
  },  
});

/**
 * Chart wrapper
 */
var ChartHeader = React.createClass({ 
  render: function() {
    var subs = this.props.subs;
    return (
      <div
        className="subheading"
      >
        <h3>
          {this.props.id + 1}: {this.props.caption}        
        </h3>
        <ul>
          {subs.map(function(sub, index){
            return <li key={index}>{sub}</li>;
          })}
        </ul>
      </div>
    );
  }
});

/**
 * Chart
 */
var Chart = React.createClass({
  _hideChart: function() {
    return this.props.countFiltered;
  },
  _hasForm: function() {
    return this.props.countries.length || this.props.regions.length || this.props.specialties.length;
  },
  // This method is not called for the initial render or when forceUpdate is used.
  shouldComponentUpdate: function(nextProps, nextState) {
    return nextProps.needsRender;
  },
  render: function() {
    var subHeadings = [];
    subHeadings[subHeadings.length] = 'Filtered: ' + this.props.countFiltered + ', Total: ' + this.props.countTotal;
    subHeadings[subHeadings.length] = 'Selected Country: ' + (this.props.filters.country || 'All');
    subHeadings[subHeadings.length] = 'Selected Region: ' + (this.props.filters.region || 'All');
    subHeadings[subHeadings.length] = 'Selected Specialty: ' + (this.props.filters.specialty || 'All');
    
    var _toggleLoader = this._toggleLoader;
    return (
      <div>
          <div 
            className="chart-container"
            style={{ 
              margin: '30px 20px 20px', 
              border: '1px solid #DDD', 
              padding: '10px', 
            }} 
          >
            <ChartHeader 
              caption = {this.props.chartmachinename || this.props.charttype}
              subs    = {subHeadings}
              id      = {this.props.id}
            />
            { this.props.isLoadingChart == this.props.chartmachinename ?
              <Loader 
                style = {{ minHeight: "310px", width: "305px" }} 
              /> 
            : null }
            <GoogleChart 
              style = {{ minHeight: "500px", width: "700px" }}
              chartmachinename = {this.props.chartmachinename}
              charttype = {this.props.charttype}
              datatable = {this.props.datatable}
              id = {this.props.id}
              isLoadingChart = {(this.props.isLoadingChart == this.props.chartmachinename ? true : false)}
            /> 
            { this._hasForm() ? 
              <ChartForm 
                chartmachinename={this.props.chartmachinename} 
                countries={this.props.countries} 
                specialties={this.props.specialties}
                regions={this.props.regions}
                loadCharts={this.props.loadCharts}
                filters={this.props.filters}
              />  
            : null }
          </div>
      </div>
    );
  }  
});

/**
 * ChartList
 */
var ChartList = React.createClass({  
  getInitialState: function() {
    return {
      data: typeof samplechartsdata == 'undefined' ? [] : samplechartsdata,
      isLoadingChart: false
    };
  },  
  componentDidMount: function() {
    this._getChartsData();
  }, 
  _needsRender: null, 
  _getChartsData: function(request){
    var request = request || {};
    
    // optimistic update
    this._needsRender = request.chartmachinename; // used in Chart.shouldComponentUpdate
    this.setState({
      data: this.state.data,
      isLoadingChart: request.chartmachinename || this.state.isLoadingChart
    });
    
    var $element = $('#react-content');
    var url = $element.data('charts_data_url');
    request.order_id = $element.data('order_id');
    $.ajax({
      url: url,
      dataType: 'json',
      type: 'POST',
      data: request,
      success: function(data) {
        switch(data.status){
          case 200:
            this._needsRender = request.chartmachinename;
            this.setState({
              data: data.content || [],
              isLoadingChart: false
            });
          break;
          default:
            console.error(url, data.status, data.content.toString());
          break;
        }
      }.bind(this),
      error: function(xhr, status, err) {
        console.error(url, status, err.toString());
      }.bind(this),
    });    
  },  
  render: function() {
    var needsRender = this._needsRender;
    var isLoadingChart = this.state.isLoadingChart;
    var loadCharts = this._getChartsData;
    var chartNodes = this.state.data.map(function(chart, index) {
      var drill = chart.drilldown || {};
      return (
        <Chart 
          key                   = {index} 
          id                    = {index}
          chartmachinename      = {chart.chartmachinename} 
          charttype             = {chart.charttype} 
          datatable             = {chart.datatable || {}} 
          countries             = {drill.countries || []}
          specialties           = {drill.specialties || []}
          regions               = {drill.regions || []}
          loadCharts            = {loadCharts}
          filters               = {chart.filter}
          countTotal            = {chart.countTotal}
          countFiltered         = {chart.countFiltered}
          isLoadingChart        = {isLoadingChart}
          needsRender           = {(needsRender == chart.chartmachinename)}
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
  <ChartList />,
  document.getElementById('react-content')
);
