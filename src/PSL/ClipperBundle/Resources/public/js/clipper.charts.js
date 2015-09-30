/**
 * Clipper Charts
 * @author Alexys Hegmann <alexys.hegmann@pslgroup.com>
 */

// Namespace
var clipper = clipper || {};
clipper.charts = clipper.charts || {};

/**
 * Initialize the Google Visualization API. You DO can call this function multiple
 * times. It will check the previous states of initialization and just wait for the
 * resources to be available before calling the callback.
 * 
 * @param function callback
 */
	clipper.charts.initialize = function(callback) {
		// Look for a G JSAPI script tag.
		var allScripts = document.getElementsByTagName('script');
		var gapiScriptTagExists = false;
		for (var i = 0; i < allScripts.length; i++) {
			if (allScripts[i].src == 'https://www.google.com/jsapi') {
				gapiScriptTagExists = true;
			}
		}
		// If G JSAPI exists, wait for visualization API and charts modules
		// to be available.
		if (gapiScriptTagExists) {
			var tmr = setInterval(function() {
				try {
					if (!google.hasOwnProperty('visualization')) throw 'visualization api not loaded';
					if (!google.hasOwnProperty('charts')) throw 'charts module not loaded';
					clearInterval(tmr);
					callback();
				} catch(e) {}
			}, 50);
			return;
		}
		// If G JSAPI doesn't exist, create one.
		var head = document.getElementsByTagName('head')[0];
		var s = document.createElement('script');
		s.type = "text/javascript";
		s.async = true;
		s.src = 'https://www.google.com/jsapi';
		s.onreadystatechange = s.onload = function() {
			var state = s.readyState;
			if (!state || /loaded|complete/.test(state)) {
				google.load('visualization', '1', {
					packages: ['corechart', 'bar', 'orgchart'],
					callback: function() {
						callback();
					}
				});
			}
		};

		head.appendChild(s);
	};
// END Initialization

/**
 * Chart Base object
 */
	clipper.charts.Chart = function(id, settings, data) {
		// Check dependencies
		if (typeof google == 'undefined' && typeof google.visualization == 'undefined') {
			throw 'Google Visualization API must be loaded before creating charts.';
		}

		if (typeof id == 'string' && document.getElementById(id) == null) throw 'DOM element ID "' + id + '" not found.';

		this.id = id;

		var defaultSettings = {
			logo: {
				image: 'none',
				width: '0px',
				height: '0px',
				opacity: 0.3,
				position: 'bottom right'
			}
		};
		if (settings) {
			this.settings = clipper._merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this._data = data || [];

	};

	clipper.charts.Chart.prototype.setData = function(data, redraw) {
		var rd = redraw || false;
		this._data = data;
		if (rd) this.draw();
	};

	/**
	 * Returns the current data table
	 */
	clipper.charts.Chart.prototype.getData = function() {
		return this._data;
	};

	/**
	 * Adds a row in the data table
	 * @param object data
	 *   The new object to add. It will be merged with the default row, if exists.
	 */
	clipper.charts.Chart.prototype.addRow = function(data, redraw) {
		var rd = redraw || false;
		this._data.push(data);
		if (rd) this.draw();
	};

	/**
	 * Deletes a row from the data table
	 *  @param int index
	 *    Which element to delete
	 */
	clipper.charts.Chart.prototype.delRow = function(index, redraw) {
		var rd = redraw || false;
		if (index >= 0 && index < this._data.length) {
			return this._data.splice(index, 1)[0];
		} else {
			return null;
		}
		if (rd) this.draw();
	};

	clipper.charts.Chart.prototype.getLogo = function() {
		var logo = document.createElement('div');
		logo.style.position = 'absolute';
		logo.style.opacity = this.settings.logo.opacity;
		logo.style.filter = 'alpha(opacity=' + (parseFloat(this.settings.logo.opacity) * 100) + ')';
		logo.style.backgroundSize = 'contain';
		logo.style.backgroundRepeat = 'no-repeat';
		logo.style.backgroundPosition = 'center center';
		logo.style.height = this.settings.logo.height;
		logo.style.width = this.settings.logo.width;
		logo.style.backgroundImage = 'url(\'' + this.settings.logo.image + '\')';
		if (this.settings.logo.position.indexOf('top') > -1) {
			logo.style.top = '10px';
		} else {
			logo.style.bottom = '10px';
		}
		if (this.settings.logo.position.indexOf('left') > -1) {
			logo.style.left = '10px';
		} else {
			logo.style.right = '10px';
		}
		return logo;
	}

	// Abstract method
	clipper.charts.Chart.prototype.draw = function() { throw 'Draw method must be overriden by child class.'; };
// END Chart Base object

clipper.charts.factory = function(type, id, settings, data) {
	if (!clipper.charts.hasOwnProperty(type)) throw 'Chart type "' + type + '" does not exist.';

	if (settings != null && settings.hasOwnProperty('formatter')) {
		if (!clipper.charts.formatters.hasOwnProperty(settings.formatter)) throw 'Chart data formatter "' + setting.formatter + '" does not exist.';
		data = clipper.charts.formatters[settings.formatter](data);
	}

	// Add class to the wrapper
	var wrapper = document.getElementById(id);
	if (wrapper) {
		var machineName = type.replace('_', '');
		machineName = machineName.toLowerCase();
		clipper._injectClass('clipper-charts-' + machineName, wrapper);
	}

	return new clipper.charts[type](id, settings, data);
};

/**
 * NPS Chart
 */
	clipper.charts.NPS_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

		if (typeof google.visualization.BarChart == 'undefined') {
			throw 'Google Visualization API must be loaded with the BarChart module before creating charts.';
		}

		var defaultSettings = {
			detractorsBar: {
				fill: '#dc7629',
				strokeWidth: 1,
				strokeOpacity: 1,
				strokeColor: '#cccccc'
			},
			passivesBar: {
				fill: '#ffffff',
				strokeWidth: 0,
				strokeOpacity: 0,
				strokeColor: '#ffffff'
			},
			promotersBar: {
				fill: '#6299d4',
				strokeWidth: 1,
				strokeOpacity: 1,
				strokeColor: '#cccccc'
			},
			textColor: '#aaa',
			textFont: 'sans-serif'
		};

		defaultSettings = clipper._merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper._merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this.draw();
	}
	clipper.charts.NPS_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.NPS_Chart.constructor = clipper.charts.NPS_Chart;

	clipper.charts.NPS_Chart.prototype.getValueBoundaries = function() {
		var min = 0;
		var max = 0;
		for (var i = 0; i < this._data.length; i++) {
			if (this._data[i].detractors > min) {
				min = this._data[i].detractors;
			}
			if (this._data[i].promoters > max) {
				max = this._data[i].promoters;
			}
		};
		return {
			min: min * -1,
			max: max
		}
	};

	clipper.charts.NPS_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';
		this._gchart = new google.visualization.BarChart(document.getElementById(this.id));

		var boundaries = this.getValueBoundaries();

		var options = {
			isStacked: 'true',
			bar: {
				groupWidth: '80%'
			},
			colors: [
				this.settings.detractorsBar.fill,
				this.settings.passivesBar.fill,
				this.settings.promotersBar.fill
			],
			hAxis: {
				textPosition: 'none',
				gridlines: {
					count: 0
				},
				viewWindow: {
					min: boundaries.min - 0.05,
					max: boundaries.max + 0.05 + 0.3
				}
			},
			vAxis: {
				textStyle: {
					color: this.settings.textColor,
					fontSize: '13'
				}
			},
			legend: {
				position: 'none',
				textStyle: {
					color: this.settings.textColor
				}
			},
			annotations: {
				highContrast: true,
				textStyle: {
					fontSize: '13',
				}
			},
			tooltip: {
				trigger: 'select'
			}
		};

		// Create Data Table.
		var dt = new google.visualization.DataTable({
			cols: [
				{ id: 'brand', label: 'brand', type: 'string' },
				{ id: 'detractors', label: 'detractors', type: 'number' },
				{ type: 'string', role: 'annotation' },
				{ type: 'string', role: 'tooltip' },
				{ type: 'string', role: 'style' },
				{ id: 'passives', label: 'passives', type: 'number' },
				{ type: 'string', role: 'annotation' },
				{ type: 'string', role: 'tooltip' },
				{ type: 'string', role: 'style' },
				{ id: 'promoters', label: 'promoters', type: 'number' },
				{ type: 'string', role: 'annotation' },
				{ type: 'string', role: 'tooltip' },
				{ type: 'string', role: 'style' }
			]
		});

		// Populate Data.
		for (var idx = 0; idx < this._data.length; idx++) {
			// Defaults.
			if (!this._data[idx].brand) { this._data[idx].brand = 'undefined'; }
			if (!this._data[idx].detractors) { this._data[idx].detractors = 0; }
			if (!this._data[idx].passives) { this._data[idx].passives = 0; }
			if (!this._data[idx].promoters) { this._data[idx].promoters = 0; }

			dt.addRow([
				this._data[idx].brand,
				this._data[idx].detractors * -1,
				(this._data[idx].detractors * 100) + '%',
				this._data[idx].brand + "\n" + (this._data[idx].detractors * 100) + '%',
				'stroke-width: ' + this.settings.detractorsBar.strokeWidth + '; stroke-color: ' + this.settings.detractorsBar.strokeColor + '; stroke-opacity: ' + this.settings.detractorsBar.strokeOpacity,
				//this._data[idx].passives,
				0.3,
				(this._data[idx].passives * 100) + '%',
				this._data[idx].brand + "\n" + (this._data[idx].passives * 100) + '%',
				'stroke-width: ' + this.settings.passivesBar.strokeWidth + '; stroke-color: ' + this.settings.passivesBar.strokeColor + '; stroke-opacity: ' + this.settings.passivesBar.strokeOpacity,
				this._data[idx].promoters,
				(this._data[idx].promoters * 100) + '%',
				this._data[idx].brand + "\n" + (this._data[idx].promoters * 100) + '%',
				'stroke-width: ' + this.settings.promotersBar.strokeWidth + '; stroke-color: ' + this.settings.promotersBar.strokeColor + '; stroke-opacity: ' + this.settings.promotersBar.strokeOpacity
			]);
		}

		this._gchart.draw(dt, options);

		// Create Score labels.
		var cli = this._gchart.getChartLayoutInterface();
		var chartArea = cli.getChartAreaBoundingBox();
		var wrapper = document.querySelector('[id="' + this.id + '"] > div:first-child');
		var overlay = document.createElement('div');
		var overlay_bar = null;
		var overlay_text = document.createTextNode('Score');

		var overlay_style = overlay.style;
		overlay_style.left = (chartArea.left + chartArea.width) + 'px';
		overlay_style.top = chartArea.top - 20 + "px";
		overlay_style.position = 'absolute';
		overlay_style.width = wrapper.offsetWidth - (chartArea.left + chartArea.width) + 'px';
		overlay_style.color = this.settings.textColor;
		overlay_style.fontFamily = this.settings.textFont;
		overlay_style.textAlign = 'center';
		overlay_style.fontSize = '13px';
		overlay.appendChild(overlay_text);
		wrapper.appendChild(overlay);

		var b2 = cli.getBoundingBox('bar#1#0');
		overlay = document.createElement('div');
		overlay_style = overlay.style;
		overlay_style.left = chartArea.left + 'px';
		overlay_style.top = chartArea.top - 20 + "px";
		overlay_style.position = 'absolute';
		overlay_style.width = (b2.left - chartArea.left) + 'px';
		overlay_style.color = this.settings.textColor;
		overlay_style.fontFamily = this.settings.textFont;
		overlay_style.textAlign = 'center';
		overlay_style.fontSize = '13px';
		overlay_text = document.createTextNode('Detractors');
		overlay.appendChild(overlay_text);
		wrapper.appendChild(overlay);

		var b3 = cli.getBoundingBox('bar#2#0');
		overlay = document.createElement('div');
		overlay_style = overlay.style;
		overlay_style.left = b2.left + 'px';
		overlay_style.top = chartArea.top - 20 + "px";
		overlay_style.position = 'absolute';
		overlay_style.width = (b3.left - b2.left) + 'px';
		overlay_style.color = this.settings.textColor;
		overlay_style.fontFamily = this.settings.textFont;
		overlay_style.textAlign = 'center';
		overlay_style.fontSize = '13px';
		overlay_text = document.createTextNode('Passives');
		overlay.appendChild(overlay_text);
		wrapper.appendChild(overlay);

		overlay = document.createElement('div');
		overlay_style = overlay.style;
		overlay_style.left = b2.left + b2.width + 'px';
		overlay_style.top = chartArea.top - 20 + "px";
		overlay_style.position = 'absolute';
		overlay_style.width = (chartArea.width + chartArea.left - (b2.left + b2.width)) + 'px';
		overlay_style.color = this.settings.textColor;
		overlay_style.fontFamily = this.settings.textFont;
		overlay_style.textAlign = 'center';
		overlay_style.fontSize = '13px';
		overlay_text = document.createTextNode('Promoters');
		overlay.appendChild(overlay_text);
		wrapper.appendChild(overlay);

		var overlay_bar_label = null;

		for (var idx = 0; idx < this._data.length; idx++) {
			if (!this._data[idx].score) { this._data[idx].score = 0; }
			overlay = document.createElement('div');
			overlay_bar = document.createElement('div');
			overlay_bar_label = document.createElement('div');
			overlay_style = overlay.style;
			overlay_style.position = 'absolute';
			overlay_style.left = (chartArea.left + chartArea.width) + 'px';
			overlay_style.top = Math.floor(cli.getBoundingBox('bar#0#' + idx).top) + "px";
			overlay_style.height = Math.floor(cli.getBoundingBox('bar#0#' + idx).height) + "px";
			overlay_style.width = wrapper.offsetWidth - (chartArea.left + chartArea.width) + 'px';
			overlay_style.color = this.settings.textColor;
			overlay_style.fontFamily = this.settings.textFont;
			overlay_style.textAlign = 'center';
			overlay_style.fontSize = '13px';
			overlay_style.backgroundColor = '#ffffff';
			var scoreText = (this._data[idx].score.toString());
			if (scoreText.indexOf('.') > -1) {
				scoreText = scoreText.substring(0, scoreText.indexOf('.') + 3);
			}
			overlay_text = document.createTextNode(scoreText);
			overlay_bar.style.position = 'absolute';
			overlay_bar.style.height = '100%';
			if (this._data[idx].score > 0) {
				overlay_bar.style.width = (this._data[idx].score * 0.5) + '%';
				overlay_bar.style.backgroundColor = '#cddcab';
				overlay_bar.style.left = '50%';
				overlay_bar_label.style.position = 'absolute';
				overlay_bar_label.style.top = '50%';
				overlay_bar_label.style.transform = 'translateY(-50%)';
				overlay_bar_label.style.right = '-25px';
			} else if (this._data[idx].score == 0) {
				overlay_bar.style.width = '1px';
				overlay_bar.style.backgroundColor = '#7f7f7f';	
				overlay_bar.style.left = '50%';
				overlay_bar_label.style.transform = 'translateY(-50%)';
				overlay_bar_label.style.position = 'absolute';
				overlay_bar_label.style.top = '50%';
				overlay_bar_label.style.right = '-25px';
			} else {
				overlay_bar.style.width = (this._data[idx].score * -1 * 0.5) + '%';
				overlay_bar.style.backgroundColor = '#e2a8a5';
				overlay_bar.style.right = '50%';
				overlay_bar_label.style.position = 'absolute';
				overlay_bar_label.style.top = '50%';
				overlay_bar_label.style.transform = 'translateY(-50%)';
				overlay_bar_label.style.left = '-25px';
			}
			overlay.appendChild(overlay_bar);
			overlay_bar_label.appendChild(overlay_text);
			overlay_bar.appendChild(overlay_bar_label);
			wrapper.appendChild(overlay);
		}

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

	}
// END NPS Chart

/**
 * How loyal are doctors to my brand Chart
 */
	clipper.charts.Loyalty_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

		if (typeof google.visualization.BubbleChart == 'undefined') {
			throw 'Google Visualization API must be loaded with the BubbleChart module before creating charts.';
		}

		var defaultSettings = {
			bubbles: {
				opacity: 1,
				strokeColor: '#ffffff'
			},
			brandBubble: {
				fill: '#919191'
			},
			meanBubble: {
				fill: '#ff2a1a',
			},
			textColor: '#aaa',
			textFont: 'sans-serif',
			textWeight: 'normal',
		};

		defaultSettings = clipper._merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper._merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this.draw();
	};
	clipper.charts.Loyalty_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.Loyalty_Chart.constructor = clipper.charts.Loyalty_Chart;

	clipper.charts.Loyalty_Chart.prototype.getBoundaries = function() {
		var min = 0,
			max = 0;

		for (var i = 0; i < this._data.brands.length; i++) {
			var cv = this._data.brands[i].loyalty;
			if (cv > max) max = cv;
			if (cv < min) min = cv;
		}

		return {
			// min: min,
			// max: max
			min: 1,
			max: 5
		};
	};	

	clipper.charts.Loyalty_Chart.prototype.getMean = function() {
		if (this._data.hasOwnProperty('mean')) return this._data.mean;
		if (this._data.brands.length < 1) return 0;
		var total = 0;
		for (var i = 0; i < this._data.brands.length; i++) {
			total += this._data.brands[i].loyalty;
		}
		return total / this._data.brands.length;
	};

	clipper.charts.Loyalty_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';
		this._gchart = new google.visualization.BubbleChart(document.getElementById(this.id));

		var brands = this._data.brands;

		var boundaries = this.getBoundaries();
		var max = Math.ceil(boundaries.max);
		var min = Math.floor(boundaries.min);
		var ticks = [];
		for (var t = min; t <= max; t++) {
			ticks.push(t);
		}

		var options = {
			colors: [this.settings.brandBubble.fill, this.settings.meanBubble.fill],
			hAxis: {
				ticks: ticks,
				gridlines: {
					count: max - min + 2,
					color: '#ffffff'
				}
			},
			vAxis: {
				minValue: 0,
				textPosition: 'none',
				gridlines: {
					count: brands.length + 3
				}
			},
			legend: {
				position: 'none'
			},
			sizeAxis: {
				maxSize: 13
			},
			tooltip: {
				trigger: 'none'
			},
			bubble: {
				opacity: this.settings.bubbles.opacity,
				stroke: this.settings.bubbles.strokeColor
			}
		};

		// Create Data Table.
		var dt = new google.visualization.DataTable({
			cols: [
				{ id: 'brand', label: 'brand', type: 'string' },
				{ id: 'loyalty', label: 'loyalty', type: 'number' },
				{ id: 'count', label: 'count', type: 'number' },
				{ type: 'string', role: 'style' },
			]
		});

		// Populate Data.
		var data = Object.create(brands);
		var mean = this.getMean();
		data.push({
			brand: 'Mean',
			loyalty: parseFloat(mean.toFixed(2))
		});

		data.sort(function(a, b) {
			return b.loyalty - a.loyalty;
		});

		for (var idx = 0; idx < data.length; idx++) {
			// Defaults.
			if (!data[idx].brand) { data[idx].brand = 'undefined'; }
			if (!data[idx].loyalty) { data[idx].loyalty = 0; }

			if (data[idx].brand == 'Mean') {
				dt.addRow([
					'',
					data[idx].loyalty,
					(data.length - idx),
					'color: ' + this.settings.meanBubble.fill + ';'
				]);
			} else {
				dt.addRow([
					'',
					data[idx].loyalty,
					(data.length - idx),
					'color: ' + this.settings.brandBubble.fill + ';'
				]);
			}

		}
		

		this._gchart.draw(dt, options);

		// Create Score labels.
		var cli = this._gchart.getChartLayoutInterface();
		var chartArea = cli.getChartAreaBoundingBox();
		var wrapper = document.querySelector('[id="' + this.id + '"] > div:first-child');
		var overlay = null;
		var overlay_values = null;
		for (var idx = 0; idx < data.length; idx++) {
			var overlay = document.createElement('div');
			var overlay_style = overlay.style;
			overlay_style.textAlign = 'right';
			overlay_style.width = (chartArea.left - 15) + "px";
			overlay_style.left = "10px";
			overlay_style.top = Math.floor(cli.getYLocation((data.length - idx))) - 9 + "px";
			overlay_style.position = 'absolute';
			overlay_style.color = this.settings.textColor;
			overlay_style.fontFamily = this.settings.textFont;
			overlay_style.fontSize = '13px';
			overlay_style.fontWeight = this.settings.textWeight;
			var overlay_text = document.createTextNode(data[idx].brand);
			overlay.appendChild(overlay_text);
			wrapper.appendChild(overlay);

			overlay_values = document.createElement('div');
			overlay_values.style.position = 'absolute';
			overlay_values.style.color = this.settings.textColor;
			overlay_values.style.fontFamily = this.settings.textFont;
			overlay_values.style.fontSize = '13px';
			overlay_values.style.fontWeight = this.settings.textWeight;
			overlay_values.style.left = chartArea.width + chartArea.left + 10 + 'px';
			overlay_values.style.top = cli.getBoundingBox('vAxis#0#gridline#' + (data.length - idx)).top - 9 + 'px';
			var txt = data[idx].loyalty.toString();
			if (txt.indexOf('.') > txt.length - 3) {
				for (var i = 0; i < txt.indexOf('.') - (txt.length-3); i++) {
					txt += '0';
				}
			} else if (txt.indexOf('.') < txt.length - 3) {
				txt = txt.substring(0, txt.indexOf('.') + 3);
			} else if (txt.indexOf('.') == -1) {
				txt += '.00';
			}
			overlay_text = document.createTextNode(txt);
			overlay_values.appendChild(overlay_text);
			wrapper.appendChild(overlay_values);
		}

		// Create tick lines
		for (var i = 0; i < ticks.length; i++) {
			overlay = document.createElement('div');
			overlay_style = overlay.style;
			overlay_style.position = 'absolute';
			overlay_style.left = cli.getBoundingBox('hAxis#0#gridline#' + i).left + "px";
			overlay_style.top = chartArea.top + chartArea.height - 15 + "px";
			overlay_style.width = '1px';
			overlay_style.height = '15px';
			overlay_style.backgroundColor = '#333';
			wrapper.appendChild(overlay);
		}

		// Delete top line
		overlay = document.createElement('div');
		overlay_style = overlay.style;
		overlay_style.position = 'absolute';
		overlay_style.left = chartArea.left + 'px';
		overlay_style.top = chartArea.top + 'px';
		overlay_style.height = '5px';
		overlay_style.width = chartArea.width + 'px';
		overlay_style.backgroundColor = '#ffffff';
		wrapper.appendChild(overlay);

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

	};
// END How loyal are doctors to my brand Chart

/**
 * How many brands does a doctor promote Chart
 */
	clipper.charts.DoctorsPromote_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

		if (typeof google.visualization.OrgChart == 'undefined') {
			throw 'Google Visualization API must be loaded with the OrgChart module before creating charts.';
		}

		var defaultSettings = {
			allDoctors: {
				fill: '#cccccc',
				color: '#ffffff'
			},
			dissatisfied: {
				fill: '#dc7629',
				color: '#ffffff',
			},
			satisfied: {
				fill: '#6299d4',
				color: '#ffffff'
			},
			exclusive: {
				fill: '#6299d4',
				color: '#ffffff'
			},
			shared: {
				fill: '#6299d4',
				color: '#ffffff'
			},
			textFont: 'sans-serif'
		};

		defaultSettings = clipper._merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper._merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this.draw();
	};
	clipper.charts.DoctorsPromote_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.DoctorsPromote_Chart.constructor = clipper.charts.DoctorsPromote_Chart;

	clipper.charts.DoctorsPromote_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';
		this._gchart = new google.visualization.OrgChart(document.getElementById(this.id));

		var options = {
			size: 'medium',
			allowHtml: true
		};

		// Create Data Table.
		var dt = new google.visualization.DataTable();
		dt.addColumn('string', 'ID');
		dt.addColumn('string', 'Parent');
		dt.addColumn('number', 'Tooltip');

		// Populate Data.
		dt.addRow([ {v:'All Doctors', f:'All doctors'}, '', 0 ]);
		dt.setRowProperty(0, 'style', 'padding: 1em; font-family: ' + this.settings.textFont + '; font-weight: normal; color: ' + this.settings.allDoctors.color + '; background: ' + this.settings.allDoctors.fill + '; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Dissatisfied', f:'<big style="font-size:2em">' + (this._data.dissatisfied.amount * 100) + '%</big><br><strong>Dissatisfied</strong><br><small>(0 brands promoted)</small>'}, 'All Doctors', this._data.dissatisfied.amount ]);
		dt.setRowProperty(1, 'style', 'padding: 1em; font-family: ' + this.settings.textFont + '; font-weight: normal; color: ' + this.settings.dissatisfied.color + '; background: ' + this.settings.dissatisfied.fill + '; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Satisfied', f:'<big style="font-size:2em">' + (this._data.satisfied.amount * 100) + '%</big><br><strong>Satisfied</strong><br><small>(&gt;0 brands promoted)</small>'}, 'All Doctors', this._data.satisfied.amount ]);
		dt.setRowProperty(2, 'style', 'padding: 1em; font-family: ' + this.settings.textFont + '; font-weight: normal; color: ' + this.settings.satisfied.color + '; background: ' + this.settings.satisfied.fill + '; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Exclusive', f:'<big style="font-size:2em">' + (this._data.satisfied.exclusive.amount * 100) + '%</big><br><strong>Exclusive</strong><br><small>(1 brand promoted)</small>'}, 'Satisfied', this._data.satisfied.exclusive.amount ]);
		dt.setRowProperty(3, 'style', 'padding: 1em; font-family: ' + this.settings.textFont + '; font-weight: normal; color: ' + this.settings.exclusive.color + '; background: ' + this.settings.exclusive.fill + '; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Shared', f:'<big style="font-size:2em">' + (this._data.satisfied.shared.amount * 100) + '%</big><br><strong>Shared</strong><br><small>(&gt;1 brand promoted)</small><br>'}, 'Satisfied', this._data.satisfied.shared.amount ]);
		dt.setRowProperty(4, 'style', 'padding: 1em; font-family: ' + this.settings.textFont + '; font-weight: normal; color: ' + this.settings.shared.color + '; background: ' + this.settings.shared.fill + '; border: 0px; box-shadow: none');

		this._gchart.draw(dt, options);

		// Logo
		if (this.settings.logo.image !== 'none') {
			var wrapper = document.getElementById(this.id);
			wrapper.style.position = 'relative';
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

	}
// END How many brands does a doctor promote Chart

/**
 * Amongst my Promoters, how many other brands do they promote 
 *  and which other brand is most promoted Chart
 */
	clipper.charts.PromotersPromote_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

		var defaultSettings = {
			valueType: 'absolute',
			heatmap: {
				lowerColor: [255, 255, 255], // R, G, B
				higherColor: [0, 0, 0] // R, G, B
			},
			textColor: '#aaa',
			textFont: 'sans-serif'
		};

		defaultSettings = clipper._merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper._merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this._brand_index = [];

		this.draw();
	};
	clipper.charts.PromotersPromote_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.PromotersPromote_Chart.constructor = clipper.charts.PromotersPromote_Chart;

	clipper.charts.PromotersPromote_Chart.prototype.getBoundaries = function() {
		var max = 0;
		var min = 0;
		if (this.settings.valueType === 'absolute') {
			return {
				min: 0,
				max: 1
			};
		}
		for (var i = 0; i < this._data.length; i++) {
			if (!this._data[i].hasOwnProperty('competitors')) continue;
			for (var j in this._data[i].competitors) {
				if (this._data[i].competitors[j] > max) max = this._data[i].competitors[j];
				if (this._data[i].competitors[j] < min) min = this._data[i].competitors[j];
			}
		}
		return {
			min: min,
			max: max
		}
	};

	clipper.charts.PromotersPromote_Chart.prototype.getPercent = function(value, max, min) {
		min = min || 0;
		var v = value - min;
		var M = max - min;
		return (v / M);
	};

	clipper.charts.PromotersPromote_Chart.prototype.getColorHex = function(percent, min, max) {
		var val = Math.abs(Math.floor(min + ((max - min) * percent)));
		var hex = val.toString(16);
		if (hex.length < 2) hex = '0' + hex;
		return hex;
	};

	clipper.charts.PromotersPromote_Chart.prototype.getColor = function(percent) {
		var heatmap = this.settings.heatmap;

		var r = this.getColorHex(percent, heatmap.lowerColor[0], heatmap.higherColor[0]);
		var g = this.getColorHex(percent, heatmap.lowerColor[1], heatmap.higherColor[1]);
		var b = this.getColorHex(percent, heatmap.lowerColor[2], heatmap.higherColor[2]);

		return '#' + r + g + b;
	};

	clipper.charts.PromotersPromote_Chart.prototype.getTopMargin = function() {
		var el = document.createElement('div');
		el.style.display = 'block';
		el.style.maxWidth = '85px';
		el.style.overflow = 'hidden';
		var txt = '';
		for (var i = 0; i < this._data.length; i++) {
			if (txt.length < this._data[i].brand.length) {
				txt = this._data[i].brand;
			}
		}
		var el_txt = document.createTextNode(txt);
		el.appendChild(el_txt);
		document.getElementsByTagName('body')[0].appendChild(el);
		var width = el.offsetWidth;
		document.getElementsByTagName('body')[0].removeChild(el);
		//return width * 0.7071; // * sin(45º)
		return width; // * sin(90º)
	};

	clipper.charts.PromotersPromote_Chart.prototype.draw_note = function(brand, value, x, y) {
		var note = document.createElement('div');
		if (this.settings.valueType === 'absolute') {
			value = (value * 100) + '%';
		}
		note.id = this.id + '-note';
		note.style.position = 'absolute';
		note.style.left = x - 65 + 'px';
		note.style.top = y - 70 + 'px';
		var noteSVG = '<svg width="112" height="57" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg">';
		noteSVG += '<g>';
		noteSVG += '  <g>';
		noteSVG += '   <path stroke="null" fill="#cccccc" fill-opacity="0.4" stroke-width="0" d="m1.93805,31.00885a0.64602,0.64602 0 0 1 -0.64602,-0.64602l0,-28.42478a0.64602,0.64602 0 0 1 0.64602,-0.64602l70.41595,0a0.64602,0.64602 0 0 1 0.64602,0.64602l0,28.42478a0.64602,0.64602 0 0 1 -0.64602,0.64602l-35.53098,0l8.39824,8.39823l-16.79647,-8.39823l-26.48675,0l0.00001,0z"/>';
		noteSVG += '<path stroke="null" fill="#cccccc" fill-opacity="0.6" stroke-width="0" d="m1.29204,30.36283a0.64602,0.64602 0 0 1 -0.64602,-0.64602l0,-28.42478a0.64602,0.64602 0 0 1 0.64602,-0.64602l70.41595,0a0.64602,0.64602 0 0 1 0.64602,0.64602l0,28.42478a0.64602,0.64602 0 0 1 -0.64602,0.64602l-35.53098,0l8.39823,8.39823l-16.79646,-8.39823l-26.48674,0z"/>';
		noteSVG += '    <path stroke="#cccccc" fill="#ffffff" d="m0.64602,29.71682a0.64602,0.64602 0 0 1 -0.64602,-0.64602l0,-28.42478a0.64602,0.64602 0 0 1 0.64602,-0.64602l70.41595,0a0.64602,0.64602 0 0 1 0.64602,0.64602l0,28.42478a0.64602,0.64602 0 0 1 -0.64602,0.64602l-35.53098,0l8.39823,8.39823l-16.79646,-8.39823l-26.48674,0z"/>';
		noteSVG += '   <g>';
		noteSVG += '    <text fill="#000000" stroke-width="0" font-weight="bold" font-size="13" font-family="Arial" y="18.55" x="7.500001" text-anchor="start">' + value + '</text>';
		noteSVG += '   </g>';
		noteSVG += '  </g>';
		noteSVG += ' </g>';
		noteSVG += '</svg>';
		note.innerHTML = noteSVG;
		return note;
	}

	clipper.charts.PromotersPromote_Chart.prototype.hnd_touch = function(e) {
		var self = e.target;
		var idx = self.getAttribute('data-brand-i');
		var j = self.getAttribute('data-brand-j');
		var cStatus = (this._data[idx].hasOwnProperty('touchStatus')) ? this._data[idx].touchStatus : '';
		if (cStatus == 'touchstart' && e.type == 'touchend') {
			var brand = this._brand_index[idx];
			var value = (this._data[idx].competitors.hasOwnProperty(this._brand_index[j])) ? this._data[idx].competitors[this._brand_index[j]] : 0;
			if (value == 0) return;
			var wrapper = document.getElementById(this.id).getElementsByTagName('div')[0];
			var x = Math.floor(e.changedTouches[0].clientX) - wrapper.getBoundingClientRect().left;
			var y = Math.floor(e.changedTouches[0].clientY) - wrapper.getBoundingClientRect().top;
			var oldNote = document.getElementById(this.id + '-note');
			if (oldNote) {
				oldNote.parentNode.removeChild(oldNote);
			}
			wrapper.appendChild(this.draw_note(brand, value, x, y));
		}
		this._data[idx].touchStatus = e.type;
	};

	clipper.charts.PromotersPromote_Chart.prototype.hnd_mouse = function(e) {
		if (e.type == 'mouseleave') {
			var oldNote = document.getElementById(this.id + '-note');
			if (oldNote) {
				oldNote.parentNode.removeChild(oldNote);
			}
		}
		if (e.type == 'mouseenter') {
			var self = e.target;
			var idx = self.getAttribute('data-brand-i');
			var j = self.getAttribute('data-brand-j');
			var brand = this._brand_index[idx];
			var value = (this._data[idx].competitors.hasOwnProperty(this._brand_index[j])) ? this._data[idx].competitors[this._brand_index[j]] : 0;
			if (value == 0) return;
			var wrapper = document.getElementById(this.id).getElementsByTagName('div')[0];
			var x = Math.floor(e.clientX) - wrapper.getBoundingClientRect().left;
			var y = Math.floor(e.clientY) - wrapper.getBoundingClientRect().top;
			var oldNote = document.getElementById(this.id + '-note');
			if (oldNote) {
				oldNote.parentNode.removeChild(oldNote);
			}
			wrapper.appendChild(this.draw_note(brand, value, x, y));
		}
	};

	clipper.charts.PromotersPromote_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';
		var wrapper = document.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.height = '100%';
		wrapper.style.width = '100%';

		document.getElementById(this.id).appendChild(wrapper);

		this._brand_index = [];
		var value = 0;
		var boundaries = this.getBoundaries();
		var color = '';

		var html = '';

		var topMarg = this.getTopMargin();

		//var overflow = (this._data.length > Math.floor((wrapper.clientHeight - topMarg) / 50)) ? 'scroll' : 'auto';
		var overflow = 'auto';

		html += '<div style="float: left; margin-top:0px;max-width:85%;overflow-x: ' + overflow + '; ">';
		html += '<table cellspacing="0" style="margin-left: 10px; margin-bottom: 15px; font-size: 12px; font-family: ' + this.settings.textFont + '; text-align: center; color: ' + this.settings.textColor + '">';

		html += '<tr><td rowspan="' + (this._data.length + 2) + '"><svg width="18" height="60"><g><text fill="' + this.settings.textColor + '" stroke-width="0" x="50%" y="50%" font-size="13" font-family="' + this.settings.textFont + '" font-weight="bold" text-anchor="middle" transform="rotate(-90 9,30) ">Brands</text></g></svg></td><td>&nbsp;</td><th colspan="' + this._data.length + '" style="padding:5px; padding-bottom: ' + topMarg + 'px;">Most Commonly Promoted Competitor</th></tr>';

		html += '<tr><td>&nbsp;</td>';
		for (var i = 0; i < this._data.length; i++) {
			html += '<th><div style="font-weight: normal; text-overflow: ellipsis; white-space: nowrap; overflow:hidden; max-width: 85px; text-align: left; position:absolute; transform: rotate(-90deg) translateX(5%) translateY(0%); transform-origin: 0% 0%" title="' + this._data[i].brand + '">' + this._data[i].brand + '</div></th>';
			this._brand_index.push(this._data[i].brand);
		}
		html += '</tr>';
		
		for (var i = 0; i < this._data.length; i++) {
			html += '<tr>';
			html += '<th style="font-weight: normal; text-align:right; padding-right: 5px" title="' + this._data[i].brand + '">' + this._data[i].brand + '</th>';
			for (var j = 0; j < this._data.length; j++) {
				value = (this._data[i].competitors.hasOwnProperty(this._brand_index[j])) ? this._data[i].competitors[this._brand_index[j]] : 0;
				var percent = this.getPercent(value, boundaries.max, boundaries.min);
				color = this.getColor(percent);
				html += '<td style="width: 50px; height: 50px; border: 1px solid #eee; background-color: ' + color + '" class="clipper-charts-promoterspromotechart-cell" data-brand-i="' + i + '" data-brand-j="' + j + '">&nbsp;</td>';
			}
			html += '</tr>';
		}
		html += '</table></div>';

		html += '<div class="clipper-charts-promoterspromotechart-legend" style="float: left; margin-top:' + topMarg + 'px;margin-left: 5%; width:10%;"><table style="font-family: ' + this.settings.textFont + '; font-size: 12px; text-align: center">';
		for (var i = 0; i <= 10; i++) {
			html += '<tr>';
			color = this.getColor(i / 10);
			html += '<td style="width: 30px; background-color: ' + color + ';"><div style="width:30px;height:30px;"></div></td>';
			html += '<td style="width: 30px; height: 30px">' + (i * 10) + '%</td>';
			html += '</tr>';
		}
		html += '</tr></table></div>';

		html += '<div style="clear:both"></div>';

		wrapper.innerHTML = html;

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

		var cells = wrapper.getElementsByClassName('clipper-charts-promoterspromotechart-cell');
		if (cells) {
			for (var i = 0; i < cells.length; i++) {
				cells[i].addEventListener('touchstart', this.hnd_touch.bind(this));
				cells[i].addEventListener('touchmove', this.hnd_touch.bind(this));
				cells[i].addEventListener('touchend', this.hnd_touch.bind(this));
				cells[i].addEventListener('mouseenter', this.hnd_mouse.bind(this));
				cells[i].addEventListener('mouseleave', this.hnd_mouse.bind(this));
			}
		}

		var tables = wrapper.getElementsByTagName('table');
		var tbl = tables[0];
		var tblh = tbl.clientHeight;
		var legend = tables[1];
		var lh = legend.clientHeight;
		if (wrapper.clientHeight <= window.innerHeight && tblh > lh) {
			var ltopmarg = Math.floor((tblh / 2) - (lh / 2));
			wrapper.getElementsByClassName('clipper-charts-promoterspromotechart-legend')[0].style.marginTop = ltopmarg + 'px';
		}

	};
// END Amongst my Promoters, how many other brands do they promote and which other brand is most promoted Chart

/**
 * Amongst my Detractors, which other brands do they promote Chart
 */
	clipper.charts.DetractorsPromote_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

		var defaultSettings = {
			valueType: 'absolute',
			heatmap: {
				lowerColor: [255, 255, 255], // R, G, B		
				higherColor: [0, 0, 0] // R, G, B		
			},		
			labelTextColor: '#aaa',		
			textColor: '#aaa',		
			textFont: 'sans-serif'
		};

		defaultSettings = clipper._merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper._merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this._brand_index = [];

		this.draw();
	};
	clipper.charts.DetractorsPromote_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.DetractorsPromote_Chart.constructor = clipper.charts.DetractorsPromote_Chart;

	clipper.charts.DetractorsPromote_Chart.prototype.getBoundaries = function() {
		var max = 0;
		var min = 0;
		if (this.settings.valueType === 'absolute') {
			return {
				min: 0,
				max: 1
			};
		}
		for (var i = 0; i < this._data.length; i++) {
			if (!this._data[i].hasOwnProperty('competitors')) continue;
			for (var j in this._data[i].competitors) {
				if (this._data[i].competitors[j] > max) max = this._data[i].competitors[j];
				if (this._data[i].competitors[j] < min) min = this._data[i].competitors[j];
			}
		}
		return {
			min: min,
			max: max
		}
	};

	clipper.charts.DetractorsPromote_Chart.prototype.getPercent = function(value, max, min) {
		min = min || 0;
		var v = value - min;
		var M = max - min;
		return (v / M);
	};

	clipper.charts.DetractorsPromote_Chart.prototype.getColorHex = function(percent, min, max) {
		var val = Math.abs(Math.floor(min + ((max - min) * percent)));
		var hex = val.toString(16);
		if (hex.length < 2) hex = '0' + hex;
		return hex;
	};

	clipper.charts.DetractorsPromote_Chart.prototype.getColor = function(percent) {
		var heatmap = this.settings.heatmap;

		var r = this.getColorHex(percent, heatmap.lowerColor[0], heatmap.higherColor[0]);
		var g = this.getColorHex(percent, heatmap.lowerColor[1], heatmap.higherColor[1]);
		var b = this.getColorHex(percent, heatmap.lowerColor[2], heatmap.higherColor[2]);

		return '#' + r + g + b;
	};

	clipper.charts.DetractorsPromote_Chart.prototype.getTopMargin = function() {
		var el = document.createElement('div');
		el.style.display = 'block';
		el.style.maxWidth = '85px';
		el.style.overflow = 'hidden';
		var txt = '';
		for (var i = 0; i < this._data.length; i++) {
			if (txt.length < this._data[i].brand.length) {
				txt = this._data[i].brand;
			}
		}
		var el_txt = document.createTextNode(txt);
		el.appendChild(el_txt);
		document.getElementsByTagName('body')[0].appendChild(el);
		var width = el.offsetWidth;
		document.getElementsByTagName('body')[0].removeChild(el);
		//return width * 0.7071; // * sin(45º)
		return width; // * sin(90º)
	};

	clipper.charts.DetractorsPromote_Chart.prototype.hnd_touch = function(e) {
		var self = e.target;
		var idx = self.getAttribute('data-brand-i');
		var j = self.getAttribute('data-brand-j');
		var cStatus = (this._data[idx].hasOwnProperty('touchStatus')) ? this._data[idx].touchStatus : '';
		if (cStatus == 'touchstart' && e.type == 'touchend') {
			var brand = this._brand_index[idx];
			var value = (this._data[idx].competitors.hasOwnProperty(this._brand_index[j])) ? this._data[idx].competitors[this._brand_index[j]] : 0;
			if (value == 0) return;
			var wrapper = document.getElementById(this.id).getElementsByTagName('div')[0];
			var x = Math.floor(e.changedTouches[0].clientX) - wrapper.getBoundingClientRect().left;
			var y = Math.floor(e.changedTouches[0].clientY) - wrapper.getBoundingClientRect().top;
			var oldNote = document.getElementById(this.id + '-note');
			if (oldNote) {
				oldNote.parentNode.removeChild(oldNote);
			}
			wrapper.appendChild(this.draw_note(brand, value, x, y));
		}
		this._data[idx].touchStatus = e.type;
	};

	clipper.charts.DetractorsPromote_Chart.prototype.hnd_mouse = function(e) {
		if (e.type == 'mouseleave') {
			var oldNote = document.getElementById(this.id + '-note');
			if (oldNote) {
				oldNote.parentNode.removeChild(oldNote);
			}
		}
		if (e.type == 'mouseenter') {
			var self = e.target;
			var idx = self.getAttribute('data-brand-i');
			var j = self.getAttribute('data-brand-j');
			var brand = this._brand_index[idx];
			var value = (this._data[idx].competitors.hasOwnProperty(this._brand_index[j])) ? this._data[idx].competitors[this._brand_index[j]] : 0;
			if (value == 0) return;
			var wrapper = document.getElementById(this.id).getElementsByTagName('div')[0];
			var x = Math.floor(e.clientX) - wrapper.getBoundingClientRect().left;
			var y = Math.floor(e.clientY) - wrapper.getBoundingClientRect().top;
			var oldNote = document.getElementById(this.id + '-note');
			if (oldNote) {
				oldNote.parentNode.removeChild(oldNote);
			}
			wrapper.appendChild(this.draw_note(brand, value, x, y));
		}
	};

	clipper.charts.DetractorsPromote_Chart.prototype.draw_note = function(brand, value, x, y) {
		var note = document.createElement('div');
		if (this.settings.valueType === 'absolute') {
			value = (value * 100) + '%';
		}
		note.id = this.id + '-note';
		note.style.position = 'absolute';
		note.style.left = x - 65 + 'px';
		note.style.top = y - 70 + 'px';
		var noteSVG = '<svg width="112" height="57" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg">';
		noteSVG += '<g>';
		noteSVG += '  <g>';
		noteSVG += '   <path stroke="null" fill="#cccccc" fill-opacity="0.4" stroke-width="0" d="m1.93805,31.00885a0.64602,0.64602 0 0 1 -0.64602,-0.64602l0,-28.42478a0.64602,0.64602 0 0 1 0.64602,-0.64602l70.41595,0a0.64602,0.64602 0 0 1 0.64602,0.64602l0,28.42478a0.64602,0.64602 0 0 1 -0.64602,0.64602l-35.53098,0l8.39824,8.39823l-16.79647,-8.39823l-26.48675,0l0.00001,0z"/>';
		noteSVG += '<path stroke="null" fill="#cccccc" fill-opacity="0.6" stroke-width="0" d="m1.29204,30.36283a0.64602,0.64602 0 0 1 -0.64602,-0.64602l0,-28.42478a0.64602,0.64602 0 0 1 0.64602,-0.64602l70.41595,0a0.64602,0.64602 0 0 1 0.64602,0.64602l0,28.42478a0.64602,0.64602 0 0 1 -0.64602,0.64602l-35.53098,0l8.39823,8.39823l-16.79646,-8.39823l-26.48674,0z"/>';
		noteSVG += '    <path stroke="#cccccc" fill="#ffffff" d="m0.64602,29.71682a0.64602,0.64602 0 0 1 -0.64602,-0.64602l0,-28.42478a0.64602,0.64602 0 0 1 0.64602,-0.64602l70.41595,0a0.64602,0.64602 0 0 1 0.64602,0.64602l0,28.42478a0.64602,0.64602 0 0 1 -0.64602,0.64602l-35.53098,0l8.39823,8.39823l-16.79646,-8.39823l-26.48674,0z"/>';
		noteSVG += '   <g>';
		noteSVG += '    <text fill="#000000" stroke-width="0" font-weight="bold" font-size="13" font-family="Arial" y="18.55" x="7.500001" text-anchor="start">' + value + '</text>';
		noteSVG += '   </g>';
		noteSVG += '  </g>';
		noteSVG += ' </g>';
		noteSVG += '</svg>';
		note.innerHTML = noteSVG;
		return note;
	}

	clipper.charts.DetractorsPromote_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';
		var wrapper = document.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.height = '100%';
		wrapper.style.width = '100%';

		document.getElementById(this.id).appendChild(wrapper);

		this._brand_index = [];
		var value = 0;
		var boundaries = this.getBoundaries();
		var color = '';

		var html = '';

		var topMarg = this.getTopMargin();

		//var overflow = (this._data.length > Math.floor((wrapper.clientHeight - topMarg) / 50)) ? 'scroll' : 'auto';
		var overflow = 'auto';

		html += '<div style="float: left; margin-top:0px;max-width:85%;overflow-x: ' + overflow + '; ">';

		html += '<table cellspacing="0" style="margin-left: 10px; margin-bottom: 15px; font-size: 12px; font-family: ' + this.settings.textFont + '; text-align: center; color: ' + this.settings.textColor + '">';

		html += '<tr><td rowspan="' + (this._data.length + 2) + '"><svg width="18" height="200"><g><text fill="' + this.settings.textColor + '" stroke-width="0" x="50%" y="50%" font-size="13" font-family="' + this.settings.textFont + '" font-weight="bold" text-anchor="middle" transform="rotate(-90 9,100) ">Detractors of these brands...</text></g></svg></td><td>&nbsp;</td><th colspan="' + this._data.length + '" style="padding:5px; padding-bottom: ' + topMarg + 'px;">...promote these brands</th></tr>';

		html += '<tr><td>&nbsp;</td>';
		for (var i = 0; i < this._data.length; i++) {
			html += '<th><div style="font-weight: normal; text-overflow: ellipsis; white-space: nowrap; overflow:hidden; max-width: 85px; text-align: left; position:absolute; transform: rotate(-90deg) translateX(5%) translateY(0%); transform-origin: 0% 0%" title="' + this._data[i].brand + '">' + this._data[i].brand + '</div></th>';
			this._brand_index.push(this._data[i].brand);
		}
		html += '</tr>';
		for (var i = 0; i < this._data.length; i++) {
			html += '<tr>';
			
			html += '<th style="font-weight: normal; text-align:right; padding-right: 5px" title="' + this._data[i].brand + '">' + this._data[i].brand + '</th>';
			for (var j = 0; j < this._data.length; j++) {
				value = (this._data[i].competitors.hasOwnProperty(this._brand_index[j])) ? this._data[i].competitors[this._brand_index[j]] : 0;
				var percent = this.getPercent(value, boundaries.max, boundaries.min);
				var color = (i == j) ? '#ffffff' : this.getColor(percent);
				var label = (i == j) ? 'X' : '&nbsp;';
				html += '<td style="width: 50px; height: 50px; border: 1px solid #eee; background-color: ' + color + '; color: ' + this.settings.labelTextColor + '" class="clipper-charts-detractorspromotechart-cell" data-brand-i="' + i + '" data-brand-j="' + j + '">' + label + '</td>';
			}
			html += '</tr>';
		}
		html += '</table></div>';

		html += '<div class="clipper-charts-detractorspromotechart-legend" style="margin-top:' + topMarg + 'px;margin-left: 5%; width:10%; float:left"><table style="font-family: ' + this.settings.textFont + '; font-size: 12px; text-align: center">';
		for (var i = 0; i <= 10; i++) {
			html += '<tr>';
			color = this.getColor(i / 10);
			html += '<td style="width: 30px; background-color: ' + color + ';"><div style="width:30px;height:30px;"></div></td>';
			html += '<td style="width: 30px; height: 30px">' + (i * 10) + '%</td>';
			html += '</tr>';
		}
		html += '</tr></table></div>';

		html += '<div style="clear:both"></div>';

		wrapper.innerHTML = html;

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

		var cells = wrapper.getElementsByClassName('clipper-charts-detractorspromotechart-cell');
		if (cells) {
			for (var i = 0; i < cells.length; i++) {
				cells[i].addEventListener('touchstart', this.hnd_touch.bind(this));
				cells[i].addEventListener('touchmove', this.hnd_touch.bind(this));
				cells[i].addEventListener('touchend', this.hnd_touch.bind(this));
				cells[i].addEventListener('mouseenter', this.hnd_mouse.bind(this));
				cells[i].addEventListener('mouseleave', this.hnd_mouse.bind(this));
			}
		}

		var tables = wrapper.getElementsByTagName('table');
		var tbl = tables[0];
		var tblh = tbl.clientHeight;
		var legend = tables[1];
		var lh = legend.clientHeight;
		if (wrapper.clientHeight <= window.innerHeight && tblh > lh) {
			var ltopmarg = Math.floor((tblh / 2) - (lh / 2));
			wrapper.getElementsByClassName('clipper-charts-detractorspromotechart-legend')[0].style.marginTop = ltopmarg + 'px';
		}

	};
// END Amongst my Detractors, which other brands do they promote Chart

/**
 * How much more of my brand do Promoters use compared to Detractors Chart
 */
	clipper.charts.PromVsDetrPromote_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

		var defaultSettings = {
			brandContainer: {
				className: 'clipper-charts-promvsdetrpromotechart-brand'
			},
			bubbles: {
				fontWeight: '700',
				textShadow: '1px 1px 2px rgba(0,0,0,1), -1px -1px 2px rgba(0,0,0,1)'
			},
			detractorsBubble: {
				fill: '#dc7629',
				textColor: '#fff'
			},
			promotersBubble: {
				fill: '#6299d4',
				textColor: '#fff'
			},
			difference: {
				textColor: '#d00',
				fontWeight: '500'
			},
			textColor: '#aaa',
			textFont: 'sans-serif'
		};

		defaultSettings = clipper._merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper._merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this.draw();
	};
	clipper.charts.PromVsDetrPromote_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.PromVsDetrPromote_Chart.constructor = clipper.charts.PromVsDetrPromote_Chart;

	clipper.charts.PromVsDetrPromote_Chart.prototype.getMax = function(item) {
		var prom = item.promoters,
			det = item.detractors;

		return (prom > det) ? prom : det;
	};

	clipper.charts.PromVsDetrPromote_Chart.prototype.getMin = function(item) {
		var prom = item.promoters,
			det = item.detractors;

		return (prom < det) ? prom : det;
	};

	clipper.charts.PromVsDetrPromote_Chart.prototype.getContainerRect = function() {
		var wrapper = document.getElementById(this.id).getElementsByTagName('div')[0];
		var dummy = document.createElement('div');
		dummy.className = this.settings.brandContainer.className;
		dummy.style.position = 'relative';
		wrapper.appendChild(dummy);
		var rect = {
			width: dummy.clientWidth,
			height: dummy.clientHeight
		};
		wrapper.removeChild(dummy);
		return rect;
	};

	clipper.charts.PromVsDetrPromote_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';
		var wrapper = document.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.width = '100%';
		document.getElementById(this.id).appendChild(wrapper);

		var containerRect = this.getContainerRect();
		// 8px * 100% / 2 (is radius)
		var minPercent = (containerRect.width == 0) ? 0 : ((8 * 100) / 2) / containerRect.width;

		var fHtml = '<div style="font-family:' + this.settings.textFont + ';font-size:13px;margin-bottom:10px;color:' + this.settings.textColor + '">';
		fHtml += '	<div style="background-color:' + this.settings.detractorsBubble.fill + ';width:26px;height:13px;display:inline-block;"></div> Detractors';
		fHtml += '	<div style="background-color:' + this.settings.promotersBubble.fill + ';width:26px;height:13px;display:inline-block;"></div> Promoters';
		fHtml += '</div>';
		var itm = null,
			Pv = 0,
			Dv = 0,
			Px = 0,
			Dx = 0;
		for (var idx = 0; idx < this._data.length; idx++) {
			var maxValue = this.getMax(this._data[idx]),
				minValue = this.getMin(this._data[idx]);
			itm = this._data[idx];
			// If maxValue is zero, we assume the value is zero and prevent div by zero.
			Pv = (maxValue != 0) ? ((itm.promoters * 0.25) / maxValue) * 100 : 0;
			Dv = (maxValue != 0) ? ((itm.detractors * 0.25) / maxValue) * 100 : 0;
			// Px = ((itm.promoters * 40) / maxValue) + 10;
			// Dx = ((itm.detractors * -40) / maxValue) + 90;
			Px = 25;
			Dx = 75;
			var svg = '<div class="' + this.settings.brandContainer.className + '" style="position:relative;">';
			svg += '	<h2 style="font-size:13px;font-family:' + this.settings.textFont + ';">' + itm.brand + '</h2>';
			svg += '	<svg width="100%" height="100%">';
			svg += '		<g>';
			svg += '			<circle cx="' + Px + '%" cy="45%" r="' + Pv + '%" fill="' + this.settings.promotersBubble.fill + '" />';
			svg += '			<circle cx="' + Dx + '%" cy="45%" r="' + Dv + '%" fill="' + this.settings.detractorsBubble.fill + '" />';
			svg += '		</g>';
			svg += '		<g>';
			var color = (Pv <= minPercent) ? this.settings.promotersBubble.fill : this.settings.promotersBubble.textColor;
			var shadow = (Pv <= minPercent) ? 'none' : this.settings.bubbles.textShadow;
			svg += '			<text x="' + Px + '%" y="45%" font-size="16" font-family="' + this.settings.textFont + '" style="fill:' + color + ';stroke-width:0;text-anchor:middle;font-weight:' + this.settings.bubbles.fontWeight + '; text-shadow: ' + shadow + '">' + Math.floor((itm.promoters * 100)) + '%</text>';
			var color = (Dv <= minPercent) ? this.settings.detractorsBubble.fill : this.settings.detractorsBubble.textColor;
			var shadow = (Dv <= minPercent) ? 'none' : this.settings.bubbles.textShadow;
			svg += '			<text x="' + Dx + '%" y="45%" font-size="16" font-family="' + this.settings.textFont + '" style="fill:' + color + ';stroke-width:0;text-anchor:middle;font-weight:' + this.settings.bubbles.fontWeight + '; text-shadow: ' + shadow + '">' + Math.floor((itm.detractors * 100)) + '%</text>';
			svg += '			<text x="50%" y="93%" font-size="16" font-family="' + this.settings.textFont + '" style="fill:' + this.settings.difference.textColor + ';text-anchor:middle;stroke-width:0;font-weight:' + this.settings.difference.fontWeight + '">' + Math.floor((itm.diff * 100)) + '%</text>';
			svg += '		</g>';
			svg += '	</svg>';
			svg += '</div>';
			fHtml += svg;
		}	

		fHtml += '<div style="clear:both;"></div>';	

		wrapper.innerHTML = fHtml;

		// Adjust height.
		var containers = wrapper.getElementsByClassName(this.settings.brandContainer.className);
		var svg = null;
		var containerWidth = 0,
			containerHeight = 0,
			containerRatio = 0;
		for (var i = 0; i < containers.length; i++) {
			containerWidth = containers[i].clientWidth;
			containerHeight = containers[i].clientHeight;
			containerRatio = containerHeight / containerWidth;
			// Our ratio must be 85% max. If it's above, scale it down.
			if (containerRatio > 0.85) {
				svg = containers[i].getElementsByTagName('svg');
				svg = svg[0] || null;
				if (svg) {
					svg.setAttribute('height', Math.floor(containerWidth * 0.85) + 'px');
					//containers[i].style.height = Math.floor(containerWidth * 0.85) + 'px';
				}
			}
		}

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

	};
// END How much more of my brand do Promoters use compared to Detractors Chart

/**
 * What brand messages are associated with Promoters, Passives and Detractors Chart
 */
	clipper.charts.PPDBrandMessages_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

		this.draw();
	};
	clipper.charts.PPDBrandMessages_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.PPDBrandMessages_Chart.constructor = clipper.charts.PPDBrandMessages_Chart;

	clipper.charts.PPDBrandMessages_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';
		this._gchart = new google.visualization.LineChart(document.getElementById(this.id));

		var options = {
			legend: 'none',
			lineWidth: 0,
			pointSize: 10,
			orientation: 'vertical',
			hAxis: {
				minValue: 0,
				maxValue: 100,
				gridlines: {
					color: '#ffffff',
					count: 11
				}
			},
			series: {
				0: { pointShape: 'triangle', color: 'red' },
				1: { pointShape: 'square', color: 'darkgrey' },
				2: { pointShape: 'circle', color: 'green' },
				3: { pointShape: 'diamond', color: '#fefefe', pointSize: 1 },
				4: { pointShape: 'diamond', color: '#fefefe', pointSize: 1 }
			}
		};

		// Create Data Table.
		var dt = new google.visualization.DataTable({
			cols: [
				{ id: 'message', label: 'message', type: 'string' },
				{ id: 'promoters', label: 'promoters', type: 'number' },
				{ id: 'passives', label: 'passives', type: 'number' },
				{ id: 'detractors', label: 'detractors', type: 'number' },
				{ id: 'LCL', label: 'Lowest confidence level', type: 'number' },
				{ id: 'HCL', label: 'Highest confidence level', type: 'number' },
			]
		});

		// Populate Data.
		for (var idx = 0; idx < this._data.length; idx++) {
			// Defaults.
			if (!this._data[idx].message) { this._data[idx].message = 'undefined'; }
			if (!this._data[idx].detractors) { this._data[idx].detractors = 0; }
			if (!this._data[idx].passives) { this._data[idx].passives = 0; }
			if (!this._data[idx].promoters) { this._data[idx].promoters = 0; }
			if (!this._data[idx].lcl) { this._data[idx].lcl = 0; }
			if (!this._data[idx].hcl) { this._data[idx].hcl = 0; }

			dt.addRow([
				this._data[idx].message,
				this._data[idx].detractors * 100,
				this._data[idx].passives * 100,
				this._data[idx].promoters * 100,
				this._data[idx].lcl * 100,
				this._data[idx].hcl * 100
			]);
		}

		this._gchart.draw(dt, options);

		// Create Score labels.
		var cli = this._gchart.getChartLayoutInterface();
		var wrapper = document.querySelector('[id="' + this.id + '"] > div:first-child');
		var overlay = null;
		var ci = null;
		var Atop = 0;
		var Aleft = 0;
		var Bleft = 0;
		var Awidth = 0;
		var clcTop = 0;
		var clcLeft = 0;
		var hlcLeft = 0;
		var ciWidth = 0;
		for (var idx = 0; idx < this._data.length; idx++) {
			// Calculate horizontal line
			Atop = Math.floor(cli.getBoundingBox('point#0#' + idx).top);
			Aleft = Math.floor(cli.getBoundingBox('point#0#' + idx).left);
			Bleft = Math.floor(cli.getBoundingBox('point#2#' + idx).left);;
			Awidth = (Bleft >= Aleft) ? Bleft - Aleft : Aleft - Bleft;
			// Create horizontal line overlay
			overlay = document.createElement('div');
			var overlay_style = overlay.style;
			overlay_style.position = 'absolute';
			overlay_style.left = ((Bleft >= Aleft) ? Aleft : Bleft) + 5 + "px";
			overlay_style.top = Atop + 6 + "px";
			overlay_style.width = Awidth + "px";
			overlay_style.height = '1px';
			overlay_style.backgroundColor = '#ccc';
			wrapper.appendChild(overlay);
			// Calculate confidence interval lines
			clcTop = Math.floor(cli.getBoundingBox('point#3#' + idx).top);
			clcLeft = Math.floor(cli.getBoundingBox('point#3#' + idx).left);
			hlcLeft = Math.floor(cli.getBoundingBox('point#4#' + idx).left);;
			ciWidth = (clcLeft >= hlcLeft) ? clcLeft - hlcLeft : hlcLeft - clcLeft;
			// Create confidence interval lines
			ci = document.createElement('div');
			ci.style.position = 'absolute';
			ci.style.left = ((clcLeft >= hlcLeft) ? hlcLeft : clcLeft) + 5 + "px";
			ci.style.top = clcTop - 5 + "px";
			ci.style.height = "20px";
			ci.style.width = ciWidth - 2 + "px";
			ci.style.borderLeft = "1px dashed #f00";
			ci.style.borderRight = "1px dashed #f00";
			wrapper.appendChild(ci);
		}

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}
	};
// END What brand messages are associated with Promoters, Passives and Detractors Chart

/**
 * What does my brand represent to Promoters as compared to Detractors Chart
 */
	clipper.charts.DNA_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

		var defaultSettings = {
			promotersSection: {
				textColor: '#558ed5',
				image: 'data:image/png;base64,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',
				backgroundColor: 'transparent'
			},
			detractorsSection: {
				textColor: '#a04e4e',
				image: 'data:image/png;base64,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',
				backgroundColor: 'transparent'
			},
			textFont: 'sans-serif'
		};

		defaultSettings = clipper._merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper._merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this.draw();
	};
	clipper.charts.DNA_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.DNA_Chart.constructor = clipper.charts.DNA_Chart;

	clipper.charts.DNA_Chart.prototype.slideUp = function(item) {
		var height = item.clientHeight;
		if (!item.hasAttribute('data-initialHeight')) {
			item.setAttribute('data-initialHeight', height);
		}
		var fHeight = 0;

		var duration = 400;
		var framerate = 20;
		var interval = duration / framerate;
		var frames = Math.ceil(duration / interval);
		var hIncrement = height / frames;
		var initialOverflow = item.style.overflow;
		item.style.overflow = 'hidden';
		var tween = function() {
			height -= hIncrement;
			item.style.height = Math.floor(height) + 'px';
			if (height > fHeight) {
				setTimeout(tween, interval);
			} else {
				item.style.display = 'none';
				item.style.overflow = initialOverflow;
			}
		}
		tween();
	};

	clipper.charts.DNA_Chart.prototype.slideDown = function(item) {
		var height = 0;
		var fHeight = parseInt(item.getAttribute('data-initialHeight'));

		var duration = 400;
		var framerate = 20;
		var interval = duration / framerate;
		var frames = Math.ceil(duration / interval);
		var hIncrement = fHeight / frames;

		var initialOverflow = item.style.overflow;
		item.style.overflow = 'hidden';
		item.style.display = 'block';
		var tween = function() {
			height += hIncrement;
			item.style.height = Math.floor(height) + 'px';
			if (height < fHeight) {
				setTimeout(tween, interval);
			} else {
				item.style.overflow = initialOverflow;
			}
		}
		tween();
	}

	clipper.charts.DNA_Chart.prototype.slideToggle = function(item) {
		var h = item.clientHeight;
		if (h > 0) {
			this.slideUp(item);
		} else {
			this.slideDown(item);
		}
	}

	clipper.charts.DNA_Chart.prototype.getCSS = function() {
		var css = '.clipper-charts-dnachart-wrapper {' +
			'	font-family: sans-serif;' +
			'	width: 100%;' + 
			'}' + 
			'.clipper-charts-dnachart-brand {' + 
			'	width: 100%;' + 
			'}' +
			'.clipper-charts-dnachart-brand > h3 {' +
			'	cursor: pointer;' + 
			'	height: 8%;' + 
			'	margin: 0px;' + 
			'	padding-top: 2%;' + 
			'}' +
			'.clipper-charts-dnachart-brand > h3.clipper-charts-dnachart-title-open:before {' +
			'	content: \'▲ \'' +
			'}' +
			'.clipper-charts-dnachart-brand > h3.clipper-charts-dnachart-title-close:before {' +
			'	content: \'▼ \'' +
			'}' +
			'.clipper-charts-dnachart-body {' +
			'	height: 88%;' + 
			'	padding-bottom: 2%;' +
			'}' +
			'.clipper-charts-dnachart-promoters, .clipper-charts-dnachart-detractors {' +
			'	width: 98%;' +
			'	margin: 0%;' +
			'	padding: 1%;' +
			'	padding-top: 70px;' +
			'	background-size: 50px;' +
			'	background-repeat: no-repeat;' +
			'	background-position: top left' +
			'}' +
			'.clipper-charts-dnachart-promoters {' +
			'	background-color: ' + this.settings.promotersSection.backgroundColor + ';' +
			'	background-image: url(' + this.settings.promotersSection.image + ');' +
			'	color: ' + this.settings.promotersSection.textColor + '' +
			'}' +
			'.clipper-charts-dnachart-detractors {' +
			'	background-color: ' + this.settings.detractorsSection.backgroundColor + ';' +
			'	background-image: url(' + this.settings.detractorsSection.image + ');' +
			'	color: ' + this.settings.detractorsSection.textColor + ';' +
			'}' +
			'.clipper-charts-dnachart-promoters ul, .clipper-charts-dnachart-detractors ul {' +
			'	font-size: 12px;' +
			'	margin: 10px;' +
			'	padding: 0px;' +
			'	list-style: none;' +
			'}' +
			'.clipper-charts-dnachart-promoters li, .clipper-charts-dnachart-detractors li {' +
			'	margin-bottom: 1em;' +
			'}';
		return css;
	};

	clipper.charts.DNA_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';

		if (document.getElementById('clipper-charts-dnachart-style') === null) {
			var style = document.createElement('style');
			style.id = 'clipper-charts-dnachart-style';
			style.innerHTML = this.getCSS();
			document.getElementsByTagName('head')[0].appendChild(style);
		}

		var wrapper = document.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.height = '100%';

		document.getElementById(this.id).appendChild(wrapper);

		var html = '<div class="clipper-charts-dnachart-wrapper">'; // Wrapper
		for (var i = 0; i < this._data.length; i++) {
			html += '<div class="clipper-charts-dnachart-brand">';
			html += '<h3 class="clipper-charts-dnachart-title-open">What is ' + this._data[i].brand + '\'s brand DNA?</h3>';
			html += '<div class="clipper-charts-dnachart-body">';
			if (this._data[i].promoters.length > 0) {
				html += '<div class="clipper-charts-dnachart-promoters">';
					html += '<h4>Promoters</h3>';
					html += '<ul>';
						for (var j = 0; j < this._data[i].promoters.length; j++) {
							html += '<li>"' + this._data[i].promoters[j] + '"</li>';
						}
					html += '</ul>';
				html += '</div>';
			}
			if (this._data[i].detractors.length > 0) {
				html += '<div class="clipper-charts-dnachart-detractors">';
					html += '<h4>Detractors</h3>';
					html += '<ul>';
						for (var j = 0; j < this._data[i].detractors.length; j++) {
							html += '<li>"' + this._data[i].detractors[j] + '"</li>';
						}
					html += '</ul>';
				html += '</div>';
			}
			html += '<div style="clear:both"></div>';
			html += '</div>';
			html += '</div>';
		}
		html += '</div>';

		wrapper.innerHTML = html;

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

		var titles = document.querySelectorAll('[id="' + this.id + '"] .clipper-charts-dnachart-brand > h3');
		for (var t = 0; t < titles.length; t++) {
			titles[t].addEventListener('click', function(e) {
				if (e.target.classList.contains('clipper-charts-dnachart-title-open')) {
					e.target.classList.remove('clipper-charts-dnachart-title-open');
					e.target.classList.add('clipper-charts-dnachart-title-close');
				} else {
					e.target.classList.remove('clipper-charts-dnachart-title-close');
					e.target.classList.add('clipper-charts-dnachart-title-open');
				}
				var parent = e.target.parentElement;
				var body = parent.getElementsByClassName('clipper-charts-dnachart-body');
				this.slideToggle(body[0]);
			}.bind(this));
		}
	};

// END What does my brand represent to Promoters as compared to Detractors Chart

/***** DATA FORMATTING **************************************************************/
clipper.charts.formatters = {
	
	NPS_Chart: function(data) {
		if (!data.hasOwnProperty('length')) throw 'Unexpected format.';
		for (var i = 0; i < data.length; i++) {
			data[i].detractors /= 100;
			data[i].passives /= 100;
			data[i].promoters /= 100;
		}
		return data;
	},

	Loyalty_Chart: function(data) {
		return data;
	},

	DoctorsPromote_Chart: function(data) {
		return {
			"satisfied": {
				"amount": data.satisfied.amount / 100,
				"exclusive": {
					"amount": data.satisfied.exclusive.amount / 100
				},
				"shared": {
					"amount": data.satisfied.shared.amount / 100
				}
			},
				"dissatisfied": {
				"amount": data.dissatisfied.amount / 100
			}
		}
	},

	PromotersPromote_Chart: function(data) {
		if (!data.hasOwnProperty('length')) throw 'Unexpected format.';
		for (var i = 0; i < data.length; i++) {
			if (data[i].competitors.hasOwnProperty('length') && data[i].competitors.length == 0) {
				data[i].competitors = {};
			}
			for (var c in data[i].competitors) {
				data[i].competitors[c] = data[i].competitors[c] / 100;
			}
		}
		return data;
	},

	DetractorsPromote_Chart: function(data) {
		if (!data.hasOwnProperty('length')) throw 'Unexpected format.';
		for (var i = 0; i < data.length; i++) {
			if (data[i].competitors.hasOwnProperty('length') && data[i].competitors.length == 0) {
				data[i].competitors = {};
			}
			for (var c in data[i].competitors) {
				data[i].competitors[c] = data[i].competitors[c] / 100;
			}
		}
		return data;
	},

	PromVsDetrPromote_Chart: function(data) {
		for (var i = 0; i < data.length; i++) {
			data[i].promoters = parseFloat(data[i].promoters) / 100;
			data[i].detractors = parseFloat(data[i].detractors) / 100;
			data[i].diff = parseFloat(data[i].diff) / 100;
		}
		return data;
	},

	PPDBrandMessages_Chart: function(data) {
		if (!data.hasOwnProperty('length')) throw 'Unexpected format.';
		for (var i = 0; i < data.length; i++) {
			data[i].detractors /= 100;
			data[i].passives /= 100;
			data[i].promoters /= 100;
			data[i].lcl /= 100;
			data[i].hcl /= 100;
		}
		return data;
	},

	DNA_Chart: function(data) {
		return data;
	}

};

/***** HELPER FUNCTIONS *************************************************************/

/**
 * Takes an object and compares it with other object (the defaults object).
 * If the first object lacks properties from the defaults, they are created.
 * Recursive.
 * @params object obj
 *   Object to test
 * @params object defaults
 *   Default object
 */
clipper._merge = function(obj, defaults) {
	for (var p in defaults) {
		if (obj.hasOwnProperty(p)) {
			if (typeof defaults[p] == 'object') {
				obj[p] = clipper._merge(obj[p], defaults[p]);
			}
		} else {
			obj[p] = defaults[p];
		}
	}
	return obj;
}

clipper._injectClass = function(className, object) {
	if (object.className.indexOf(className) > -1) return;
	if (object.className == '') {
		object.className = className;
	} else {
		object.className += ' ' + className;
	}
}

/**
 * Returns a unique id
 */
clipper.uid = function() {
	var now = Date.now();
	var rnd = (Math.random().toString(36)+'00000000000000000').slice(2,18);
	return now + '-' + rnd;
}