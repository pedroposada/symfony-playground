/**
 * This file provided by Facebook is for non-commercial testing and evaluation
 * purposes only. Facebook reserves all rights not expressly granted.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * FACEBOOK BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

var Comment = React.createClass({
  render: function() {
    var rawMarkup = marked(this.props.children.toString(), {sanitize: true});
    return (
      <div className="comment">
        <h2 className="commentAuthor">
          {this.props.author}
        </h2>
        <span dangerouslySetInnerHTML={{__html: rawMarkup}} />
      </div>
    );
  }
});

var CommentBox = React.createClass({
  
  loadCommentsFromServer: function() {
    $.ajax({
      url: this.props.url,
      dataType: 'json',
      cache: false,
      success: function(data) {
        this.setState({data: data});
      }.bind(this),
      error: function(xhr, status, err) {
        console.error(this.props.url, status, err.toString());
      }.bind(this)
    });
  },
  
  handleCommentSubmit: function(comment) {
    var comments = this.state.data;
    var newComments = comments.concat([comment]);
    this.setState({data: newComments});
    $.ajax({
      url: this.props.url,
      dataType: 'json',
      type: 'POST',
      data: comment,
      success: function(data) {
        this.setState({data: data});
      }.bind(this),
      error: function(xhr, status, err) {
        console.error(this.props.url, status, err.toString());
      }.bind(this)
    });
  },
  
  getInitialState: function() {
    return {data: []};
  },
  
  componentDidMount: function() {
    this.loadCommentsFromServer();
    setInterval(this.loadCommentsFromServer, this.props.pollInterval);
  },
  
  render: function() {
    return (
      <div className="commentBox">
        <h1>Comments</h1>
        <CommentList data={this.state.data} />
        <CommentForm onCommentSubmit={this.handleCommentSubmit} />
      </div>
    );
  }
});

var CommentList = React.createClass({
  render: function() {
    var commentNodes = this.props.data.map(function(comment, index) {
      return (
        // `key` is a React-specific concept and is not mandatory for the
        // purpose of this tutorial. if you're curious, see more here:
        // http://facebook.github.io/react/docs/multiple-components.html#dynamic-children
        <Comment author={comment.author} key={index}>
          {comment.text}
        </Comment>
      );
    });
    return (
      <div className="commentList">
        {commentNodes}
      </div>
    );
  }
});

var CommentForm = React.createClass({
  
  handleSubmit: function(e) {
    e.preventDefault();
    var author = React.findDOMNode(this.refs.author).value.trim();
    var text = React.findDOMNode(this.refs.text).value.trim();
    if (!text || !author) {
      return;
    }
    this.props.onCommentSubmit({author: author, text: text});
    React.findDOMNode(this.refs.author).value = '';
    React.findDOMNode(this.refs.text).value = '';
  },
  
  render: function() {
    return (
      <form className="commentForm" onSubmit={this.handleSubmit}>
        <input type="text" placeholder="Your name" ref="author" />
        <input type="text" placeholder="Say something..." ref="text" />
        <input type="submit" value="Post" />
      </form>
    );
  }
});


/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */


var chartsdata = [
  {
    type: "BarChart", 
    datatable: {"cols":[{"id":"b","label":"Brand","type":"string"},{"id":"P","label":"Promoters","type":"number"},{"id":"a","label":"Passives","type":"number"},{"id":"d","label":"Detractors","type":"number"},{"id":"s","label":"Score","type":"number"}],"rows":[{"c":[{"v":"AA-123"},{"v":0,"f":"0%"},{"v":0,"f":"0%"},{"v":100,"f":"100%"},{"v":-100,"f":"-100"}],"p":{"Brand":"AA-123"}},{"c":[{"v":"BB-456"},{"v":50,"f":"50%"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":0,"f":"0"}],"p":{"Brand":"BB-456"}},{"c":[{"v":"CC-789"},{"v":0,"f":"0%"},{"v":0,"f":"0%"},{"v":100,"f":"100%"},{"v":-100,"f":"-100"}],"p":{"Brand":"CC-789"}},{"c":[{"v":"DD-123"},{"v":50,"f":"50%"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":0,"f":"0"}],"p":{"Brand":"DD-123"}},{"c":[{"v":"EE-456"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":50,"f":"50%"},{"v":-50,"f":"-50"}],"p":{"Brand":"EE-456"}},{"c":[{"v":"FF-789"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":50,"f":"50%"},{"v":-50,"f":"-50"}],"p":{"Brand":"FF-789"}}]}, 
    drilldown: { 
      countries: ['USA', 'Canada'],
      specialties: ['Oncology', 'Diabetes'],
    },
    options: {
      isStacked: 'percent',
      legend : {
        position : 'top',
        maxLines : 3
      },
    }
  },
  {
    type: "what_they_say", 
    datatable: "some data here"
  }
];

// ChartForm
var ChartForm = React.createClass({
  
  handleSubmit: function(e) {
    e.preventDefault();
    var country = React.findDOMNode(this.refs.country).value.trim();
    var specialty = React.findDOMNode(this.refs.specialty).value.trim();
    this.props.loadCharts({country: country, specialty: specialty});
    // console.log(this.props);
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
  
  render: function() {
    return (
      <form className="chart-form" onSubmit={this.handleSubmit}>
        {this.Countries()}
        {this.Specialties()}
        <input type="submit" value="Filter" />
      </form>
    );
  }
})

// Chart
var Chart = React.createClass({
  
  gsChart: function(dataTable, chartId, chartType, options){
    google.setOnLoadCallback(drawChart);
    var drawChart = function(dataTable) {
      var data = new google.visualization.DataTable();
      var chart = new google.visualization.chartType(document.getElementById(chartId));
      chart.draw(data, options);
    }
  },
  
  
  render: function() {
    return (
      <div className="charts-chart">
        {/* TODO: render actual chart here */}
        
        <div id={this.props.key}>replace with chart</div>
        
        { this.props.countries.length && this.props.specialties.length ? 
          <ChartForm 
            countries={this.props.countries} 
            specialties={this.props.specialties}
            loadCharts={this.props.loadCharts}
          /> : null }
      </div>
    );
  }
});

// ChartList
var ChartList = React.createClass({
  
  loadCharts: function(drilldown) {
    var country = drilldown.country;
    var specialty = drilldown.specialty;
    console.log(country);
    console.log(specialty);
    this._getChartsData();
  },
  
  getInitialState: function() {
    return {data: []};
  },
  
  componentDidMount: function() {
    this._getChartsData();
  },
  
  _getChartsData: function(){
    this.setState({
      data: chartsdata || []
    });
  },
  
  render: function() {
    var loadCharts = this.loadCharts;
    var chartNodes = this.state.data.map(function(chart, index) {
      var drill = chart.drilldown || {};
      return (
        <Chart 
          charttype={chart.type} 
          key={index} 
          datatable={chart.datatable} 
          countries={drill.countries || []}
          specialties={drill.specialties || []}
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
  <ChartList />,
  document.getElementById('content')
);