// Namespace
var clipper = clipper || {};
clipper.charts = clipper.charts || {};

/**
 * Chart Base object
 */
	clipper.charts.Chart = function(id, settings, data) {
		// Check dependencies
		if (typeof google.visualization == 'undefined') {
			throw 'Google Visualization API must be loaded before creating charts.';
		}

		if (typeof id == 'string' && document.getElementById(id) == null) throw 'DOM element ID "' + id + '" not found.';

		this.id = id;

		var defaultSettings = {
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

	// Abstract method
	clipper.charts.Chart.prototype.draw = function() { throw 'Draw method must be overriden by child class.'; };
// END Chart Base object

clipper.charts.factory = function(type, id, settings, data) {
	if (!clipper.charts.hasOwnProperty(type)) throw 'Chart type "' + type + '" does not exist.';

	if (settings.hasOwnProperty('formatter')) {
		if (!clipper.charts.formatters.hasOwnProperty(settings.formatter)) throw 'Chart data formatter "' + setting.formatter + '" does not exist.';
		data = clipper.charts.formatters[settings.formatter](data);
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

		this.draw();
	}
	clipper.charts.NPS_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.NPS_Chart.constructor = clipper.charts.NPS_Chart;

	clipper.charts.NPS_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';
		this._gchart = new google.visualization.BarChart(document.getElementById(this.id));

		var options = {
			isStacked: 'true',
			bar: {
				groupWidth: '80%'
			},
			colors: ['#d96d20', '#f0f0f0', '#6dacdf'],

			hAxis: {
				textPosition: 'none',
				gridlines: {
					count: 2
				}
			},
			vAxis: {
				textStyle: {
					color: '#aaa',
					fontSize: '13'
				}
			},
			legend: {
				position: 'top',
				textStyle: {
					color: '#aaa'
				}
			},
			annotations: {
				highContrast: false,
				textStyle: {
					color: '#333',
					fontSize: '13',
					auraColor: '#fafafa'
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
				{ type: 'string', role: 'style' },
				{ id: 'passives', label: 'passives', type: 'number' },
				{ type: 'string', role: 'annotation' },
				{ type: 'string', role: 'style' },
				{ id: 'promoters', label: 'promoters', type: 'number' },
				{ type: 'string', role: 'annotation' },
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
				'stroke-width: 1; stroke-color: #ccc; stroke-opacity: 1',
				this._data[idx].passives,
				(this._data[idx].passives * 100) + '%',
				'stroke-width: 1; stroke-color: #ccc; stroke-opacity: 1',
				this._data[idx].promoters,
				(this._data[idx].promoters * 100) + '%',
				'stroke-width: 1; stroke-color: #ccc; stroke-opacity: 1'
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
		overlay_style = overlay.style;
		overlay_style.left = (chartArea.left + chartArea.width) + 'px';
		var vdiff = (this._data.length > 1) ? Math.floor(cli.getYLocation(1)) - Math.floor(cli.getYLocation(0)) : 40;
		overlay_style.top = Math.floor(cli.getYLocation(0)) - vdiff + "px";
		overlay_style.position = 'absolute';
		overlay_style.width = '30px';
		overlay_style.color = '#aaa';
		overlay_style.fontFamily = 'sans-serif';
		overlay_style.fontSize = '13px';
		overlay.appendChild(overlay_text);
		wrapper.appendChild(overlay);

		for (var idx = 0; idx < this._data.length; idx++) {
			if (!this._data[idx].score) { this._data[idx].score = 0; }
			overlay = document.createElement('div');
			overlay_bar = document.createElement('div');
			overlay_style = overlay.style;
			overlay_style.position = 'absolute';
			overlay_style.left = (chartArea.left + chartArea.width) + 'px';
			overlay_style.top = Math.floor(cli.getBoundingBox('bar#0#' + idx).top) + "px";
			overlay_style.height = Math.floor(cli.getBoundingBox('bar#0#' + idx).height) + "px";
			overlay_style.width = wrapper.offsetWidth - (chartArea.left + chartArea.width) + 'px';
			overlay_style.color = '#aaa';
			overlay_style.fontFamily = 'sans-serif';
			overlay_style.textAlign = 'center';
			overlay_style.fontSize = '13px';
			overlay_style.backgroundColor = '#fafafa';
			overlay_text = document.createTextNode(this._data[idx].score);
			overlay_bar.style.position = 'absolute';
			overlay_bar.style.height = '100%';
			if (this._data[idx].score > 0) {
				overlay_bar.style.width = (this._data[idx].score * 0.5) + '%';
				overlay_bar.style.backgroundColor = '#cde8cc';	
				overlay_bar.style.left = '50%';
			} else if (this._data[idx].score == 0) {
				overlay_bar.style.width = '1px';
				overlay_bar.style.backgroundColor = '#cccccc';	
				overlay_bar.style.left = '50%';
			} else {
				overlay_bar.style.width = (this._data[idx].score * -1 * 0.5) + '%';
				overlay_bar.style.backgroundColor = '#facccc';
				overlay_bar.style.right = '50%';
			}
			overlay.appendChild(overlay_bar);
			overlay_bar.appendChild(overlay_text);
			wrapper.appendChild(overlay);
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

		this.draw();
	};
	clipper.charts.Loyalty_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.Loyalty_Chart.constructor = clipper.charts.Loyalty_Chart;

	clipper.charts.Loyalty_Chart.prototype.getMean = function() {
		if (this._data.length < 1) return 0;
		var total = 0;
		for (var i = 0; i < this._data.length; i++) {
			total += this._data[i].loyalty;
		}
		return total / this._data.length;
	};

	clipper.charts.Loyalty_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';
		this._gchart = new google.visualization.BubbleChart(document.getElementById(this.id));

		var max = Math.ceil(this._data[this._data.length - 1].loyalty) + 1;
		var min = Math.floor(this._data[0].loyalty)-1;
		var ticks = [];
		for (var t = min; t <= max; t++) {
			ticks.push(t);
		}

		var options = {
			colors: ['#cccccc', '#cc0000'],
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
					count: this._data.length + 3
				}
			},
			legend: {
				position: 'none'
			},
			sizeAxis: {
				maxSize: 13
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
		var data = Object.create(this._data);
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
					'#cc0000'
				]);
			} else {
				dt.addRow([
					'',
					data[idx].loyalty,
					(data.length - idx),
					'#cccccc'
				]);
			}

		}
		

		this._gchart.draw(dt, options);

		// Create Score labels.
		cli = this._gchart.getChartLayoutInterface();
	    var chartArea = cli.getChartAreaBoundingBox();
		var wrapper = document.querySelector('[id="' + this.id + '"] > div:first-child');
		var overlay = null;
		var overlay_values = null;
		for (var idx = 0; idx < data.length; idx++) {
			overlay = document.createElement('div');
			overlay_style = overlay.style;
			overlay_style.textAlign = 'right';
			overlay_style.width = (chartArea.left - 15) + "px";
			overlay_style.left = "10px";
			overlay_style.top = Math.floor(cli.getYLocation((data.length - idx))) - 9 + "px";
			overlay_style.position = 'absolute';
			overlay_style.color = '#aaa';
			overlay_style.fontFamily = 'sans-serif';
			overlay_style.fontSize = '13px';
			overlay_text = document.createTextNode(data[idx].brand);
			overlay.appendChild(overlay_text);
			wrapper.appendChild(overlay);

			overlay_values = document.createElement('div');
			overlay_values.style.position = 'absolute';
			overlay_values.style.color = '#aaa';
			overlay_values.style.fontFamily = 'sans-serif';
			overlay_values.style.fontSize = '13px';
			overlay_values.style.left = chartArea.width + chartArea.left + 10 + 'px';
			overlay_values.style.top = cli.getBoundingBox('vAxis#0#gridline#' + (data.length - idx)).top - 9 + 'px';
			overlay_text = document.createTextNode(data[idx].loyalty);
			overlay_values.appendChild(overlay_text);
			wrapper.appendChild(overlay_values);
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
		dt.setRowProperty(0, 'style', 'padding: 1em; font-weight: normal; color: #fff; background: #ccc; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Dissatisfied', f:'<big style="font-size:2em">' + (this._data[0].dissatisfied.amount * 100) + '%</big><br><strong>Dissatisfied</strong><br><small>(0 brands promoted)</small>'}, 'All Doctors', this._data[0].dissatisfied.amount ]);
		dt.setRowProperty(1, 'style', 'padding: 1em; font-weight: normal; color: #fff; background: #d96d20; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Satisfied', f:'<big style="font-size:2em">' + (this._data[0].satisfied.amount * 100) + '%</big><br><strong>Satisfied</strong><br><small>(&gt;0 brands promoted)</small>'}, 'All Doctors', this._data[0].satisfied.amount ]);
		dt.setRowProperty(2, 'style', 'padding: 1em; font-weight: normal; color: #fff; background: #6dacdf; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Exclusive', f:'<big style="font-size:2em">' + (this._data[0].satisfied.exclusive.amount * 100) + '%</big><br><strong>Exclusive</strong><br><small>(1 brand promoted)</small>'}, 'Satisfied', this._data[0].satisfied.exclusive.amount ]);
		dt.setRowProperty(3, 'style', 'padding: 1em; font-weight: normal; color: #fff; background: #6dacdf; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Shared', f:'<big style="font-size:2em">' + (this._data[0].satisfied.shared.amount * 100) + '%</big><br><strong>Shared</strong><br><small>(&gt;1 brand promoted)</small><br>'}, 'Satisfied', this._data[0].satisfied.shared.amount ]);
		dt.setRowProperty(4, 'style', 'padding: 1em; font-weight: normal; color: #fff; background: #6dacdf; border: 0px; box-shadow: none');

		this._gchart.draw(dt, options);

	}
// END How many brands does a doctor promote Chart

/**
 * Amongst my Promoters, how many other brands do they promote 
 *  and which other brand is most promoted Chart
 */
	clipper.charts.PromotersPromote_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

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
			if (!this._data[i].hasOwnProperty('brands')) continue;
			for (var j in this._data[i].brands) {
				if (this._data[i].brands[j] > max) max = this._data[i].brands[j];
				if (this._data[i].brands[j] < min) min = this._data[i].brands[j];
			}
		}
		return {
			min: min,
			max: max
		}
	};

	clipper.charts.PromotersPromote_Chart.prototype.getPercent = function(value, max, min) {
		min = min || 0;
		v = value - min;
		M = max - min;
		return (v / M);
	};

	clipper.charts.PromotersPromote_Chart.prototype.getColor = function(percent) {
		val = 255 - Math.floor(percent * 255);
		hex = val.toString(16);
		return '#' + hex + hex + hex;
	}

	clipper.charts.PromotersPromote_Chart.prototype.getTopMargin = function() {
		var el = document.createElement('div');
		el.style.display = 'inline';
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
		return width * 0.7071; // * sin(45º)
	};

	clipper.charts.PromotersPromote_Chart.prototype.draw_note = function(brand, value, x, y) {
		var note = document.createElement('div');
		note.id = this.id + '-note';
		note.style.position = 'absolute';
		note.style.left = x - 65 + 'px';
		note.style.top = y - 70 + 'px';
		var noteSVG = '<svg width="112" height="57" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg">';
 		noteSVG += '<g>';
 		noteSVG += '  <g>';
 		noteSVG += '   <path fill="#cccccc" fill-opacity="0.4" stroke-width="0" d="m2.999998,48.000004a1,1 0 0 1 -0.999999,-1l0,-44.000005a1,1 0 0 1 0.999999,-0.999999l109.000025,0a1,1 0 0 1 1,0.999999l0,44.000005a1,1 0 0 1 -1,1l-55,0l13,13l-26,-13l-41.000025,0z"/>';
 		noteSVG += '   <path fill="#cccccc" fill-opacity="0.6" stroke-width="0" d="m1.999999,47.000004a1,1 0 0 1 -0.999999,-1l0,-44.000004a1,1 0 0 1 0.999999,-1l109.000024,0a1,1 0 0 1 1,1l0,44.000004a1,1 0 0 1 -1,1l-55,0l13,13l-26,-13l-41.000024,0z"/>';
 		noteSVG += '   <path fill="#ffffff" stroke="#cccccc" d="m0.999999,46.000004a1,1 0 0 1 -0.999999,-1l0,-44.000004a1,1 0 0 1 0.999999,-1l109.000024,0a1,1 0 0 1 1,1l0,44.000004a1,1 0 0 1 -1,1l-55,0l13,13l-26,-13l-41.000024,0z"/>';
 		noteSVG += '   <g>';
 		noteSVG += '    <text id="' + this.id + '-note-brands" class="clipper-charts-promoterspromotechart-note-brands" fill="#000000" stroke-width="0" font-weight="bold" font-size="13" font-family="Arial" y="18.55" x="7.500001" text-anchor="start">' + brand + '</text>';
 		noteSVG += '   </g>';
 		noteSVG += '   <g>';
 		noteSVG += '    <text fill="#000000" stroke-width="0" font-size="13" font-family="Arial" y="35.55" x="7.500001" text-anchor="start">Brands:</text>';
 		noteSVG += '    <text class="clipper-charts-promoterspromotechart-note-value" fill="#000000" stroke-width="0" font-weight="bold" font-size="13" font-family="Arial" y="35.55" x="55" text-anchor="start">' + value + '</text>';
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
			var value = (this._data[idx].brands.hasOwnProperty(this._brand_index[j])) ? this._data[idx].brands[this._brand_index[j]] : 0;
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

	clipper.charts.PromotersPromote_Chart.prototype.hnd_click = function(e) {
		var self = e.target;
		var idx = self.getAttribute('data-brand-i');
		var j = self.getAttribute('data-brand-j');
		var brand = this._brand_index[idx];
		var value = (this._data[idx].brands.hasOwnProperty(this._brand_index[j])) ? this._data[idx].brands[this._brand_index[j]] : 0;
		var wrapper = document.getElementById(this.id).getElementsByTagName('div')[0];
		var x = Math.floor(e.clientX) - wrapper.getBoundingClientRect().left;
		var y = Math.floor(e.clientY) - wrapper.getBoundingClientRect().top;
		var oldNote = document.getElementById(this.id + '-note');
		if (oldNote) {
			oldNote.parentNode.removeChild(oldNote);
		}
		wrapper.appendChild(this.draw_note(brand, value, x, y));
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

		html += '<div style="margin-top: ' + topMarg + 'px;margin-left: 1%; width:9%; float:right"><table style="font-family: sans-serif; font-size: 12px; text-align: center">';
		for (var i = 0; i <= 10; i++) {
			html += '<tr>';
			color = this.getColor(i / 10);
			html += '<td style="width: 30px; background-color: ' + color + ';"><div style="width:30px;height:30px;"></div></td>';
			html += '<td style="width: 30px; height: 30px">' + (i * 10) + '%</td>';
			html += '</tr>';
		}
		html += '</tr></table></div>';

		var overflow = (this._data.length > Math.floor((wrapper.clientHeight - topMarg) / 50)) ? 'scroll' : 'auto';

		html += '<div style="zoom:1;margin-top:' + topMarg + 'px;width:90%;overflow-x: ' + overflow + '; float: left;"><table cellspacing="0" style="margin-bottom: 15px; font-size: 12px; font-family: sans-serif; text-align: center; color: #aaa"><tr><td>&nbsp;</td>';
		for (var i = 0; i < this._data.length; i++) {
			html += '<th><div style="text-align: left; position:absolute; transform: rotate(-45deg) translateX(5%); transform-origin: 0% 0%">' + this._data[i].brand + '</div></th>';
			this._brand_index.push(this._data[i].brand);
		}
		html += '</tr>';
		for (var i = 0; i < this._data.length; i++) {
			html += '<tr>';
			html += '<th style="text-align:right">' + this._data[i].brand + '</th>';
			for (var j = 0; j < this._data.length; j++) {
				value = (this._data[i].brands.hasOwnProperty(this._brand_index[j])) ? this._data[i].brands[this._brand_index[j]] : 0;
				percent = this.getPercent(value, boundaries.max, boundaries.min);
				color = this.getColor(percent);
				html += '<td style="width: 50px; height: 50px; border: 1px solid #eee; background-color: ' + color + '" class="clipper-charts-promoterspromotechart-cell" data-brand-i="' + i + '" data-brand-j="' + j + '">&nbsp;</td>';
			}
			html += '</tr>';
		}
		html += '</table></div>';

		html += '<div style="clear:both"></div>';

		wrapper.innerHTML = html;

		var cells = wrapper.getElementsByClassName('clipper-charts-promoterspromotechart-cell');
		if (cells) {
			for (var i = 0; i < cells.length; i++) {
				cells[i].addEventListener('touchstart', this.hnd_touch.bind(this));
				cells[i].addEventListener('touchmove', this.hnd_touch.bind(this));
				cells[i].addEventListener('touchend', this.hnd_touch.bind(this));
				cells[i].addEventListener('click', this.hnd_click.bind(this));
			}
		}

	};
// END Amongst my Promoters, how many other brands do they promote and which other brand is most promoted Chart

/**
 * Amongst my Detractors, which other brands do they promote Chart
 */
	clipper.charts.DetractorsPromote_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

		var defaultSettings = {
			valueType: 'relative'
		};

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
			if (!this._data[i].hasOwnProperty('brands')) continue;
			for (var j in this._data[i].brands) {
				if (this._data[i].brands[j] > max) max = this._data[i].brands[j];
				if (this._data[i].brands[j] < min) min = this._data[i].brands[j];
			}
		}
		return {
			min: min,
			max: max
		}
	};

	clipper.charts.DetractorsPromote_Chart.prototype.getPercent = function(value, max, min) {
		min = min || 0;
		v = value - min;
		M = max - min;
		return (v / M);
	};

	clipper.charts.DetractorsPromote_Chart.prototype.getColor = function(percent) {
		val = 255 - Math.floor(percent * 255);
		hex = val.toString(16);
		return '#' + hex + hex + hex;
	}

	clipper.charts.DetractorsPromote_Chart.prototype.getTopMargin = function() {
		var el = document.createElement('div');
		el.style.display = 'inline';
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
		return width * 0.7071; // * sin(45º)
	};

	clipper.charts.DetractorsPromote_Chart.prototype.hnd_touch = function(e) {
		var self = e.target;
		var idx = self.getAttribute('data-brand-i');
		var j = self.getAttribute('data-brand-j');
		var cStatus = (this._data[idx].hasOwnProperty('touchStatus')) ? this._data[idx].touchStatus : '';
		if (cStatus == 'touchstart' && e.type == 'touchend') {
			var brand = this._brand_index[idx];
			var value = (this._data[idx].brands.hasOwnProperty(this._brand_index[j])) ? this._data[idx].brands[this._brand_index[j]] : 0;
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

	clipper.charts.DetractorsPromote_Chart.prototype.hnd_click = function(e) {
		var self = e.target;
		var idx = self.getAttribute('data-brand-i');
		var j = self.getAttribute('data-brand-j');
		var brand = this._brand_index[idx];
		var value = (this._data[idx].brands.hasOwnProperty(this._brand_index[j])) ? this._data[idx].brands[this._brand_index[j]] : 0;
		var wrapper = document.getElementById(this.id).getElementsByTagName('div')[0];
		var x = Math.floor(e.clientX) - wrapper.getBoundingClientRect().left;
		var y = Math.floor(e.clientY) - wrapper.getBoundingClientRect().top;
		var oldNote = document.getElementById(this.id + '-note');
		if (oldNote) {
			oldNote.parentNode.removeChild(oldNote);
		}
		wrapper.appendChild(this.draw_note(brand, value, x, y));
	};

	clipper.charts.DetractorsPromote_Chart.prototype.draw_note = function(brand, value, x, y) {
		var note = document.createElement('div');
		note.id = this.id + '-note';
		note.style.position = 'absolute';
		note.style.left = x - 65 + 'px';
		note.style.top = y - 70 + 'px';
		var noteSVG = '<svg width="112" height="57" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg">';
 		noteSVG += '<g>';
 		noteSVG += '  <g>';
 		noteSVG += '   <path fill="#cccccc" fill-opacity="0.4" stroke-width="0" d="m2.999998,48.000004a1,1 0 0 1 -0.999999,-1l0,-44.000005a1,1 0 0 1 0.999999,-0.999999l109.000025,0a1,1 0 0 1 1,0.999999l0,44.000005a1,1 0 0 1 -1,1l-55,0l13,13l-26,-13l-41.000025,0z"/>';
 		noteSVG += '   <path fill="#cccccc" fill-opacity="0.6" stroke-width="0" d="m1.999999,47.000004a1,1 0 0 1 -0.999999,-1l0,-44.000004a1,1 0 0 1 0.999999,-1l109.000024,0a1,1 0 0 1 1,1l0,44.000004a1,1 0 0 1 -1,1l-55,0l13,13l-26,-13l-41.000024,0z"/>';
 		noteSVG += '   <path fill="#ffffff" stroke="#cccccc" d="m0.999999,46.000004a1,1 0 0 1 -0.999999,-1l0,-44.000004a1,1 0 0 1 0.999999,-1l109.000024,0a1,1 0 0 1 1,1l0,44.000004a1,1 0 0 1 -1,1l-55,0l13,13l-26,-13l-41.000024,0z"/>';
 		noteSVG += '   <g>';
 		noteSVG += '    <text id="' + this.id + '-note-brands" class="clipper-charts-detractorspromotechart-note-brands" fill="#000000" stroke-width="0" font-weight="bold" font-size="13" font-family="Arial" y="18.55" x="7.500001" text-anchor="start">' + brand + '</text>';
 		noteSVG += '   </g>';
 		noteSVG += '   <g>';
 		noteSVG += '    <text fill="#000000" stroke-width="0" font-size="13" font-family="Arial" y="35.55" x="7.500001" text-anchor="start">Brands:</text>';
 		noteSVG += '    <text class="clipper-charts-detractorspromotechart-note-value" fill="#000000" stroke-width="0" font-weight="bold" font-size="13" font-family="Arial" y="35.55" x="55" text-anchor="start">' + value + '</text>';
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

		var brand_index = [];
		var value = 0;
		var boundaries = this.getBoundaries();
		var color = '';

		var html = '';

		var topMarg = this.getTopMargin();

		html += '<div style="margin-top: ' + topMarg + 'px;margin-left: 1%; width:9%; float:right"><table style="font-family: sans-serif; font-size: 12px; text-align: center">';
		for (var i = 0; i <= 10; i++) {
			html += '<tr>';
			color = this.getColor(i / 10);
			html += '<td style="width: 30px; background-color: ' + color + ';"><div style="width:30px;height:30px;"></div></td>';
			html += '<td style="width: 30px; height: 30px">' + (i * 10) + '%</td>';
			html += '</tr>';
		}
		html += '</tr></table></div>';

		var overflow = (this._data.length > Math.floor((wrapper.clientHeight - topMarg) / 50)) ? 'scroll' : 'auto';

		html += '<div style="zoom:1;margin-top:' + topMarg + 'px;width:90%;overflow-x: ' + overflow + '; float: left;"><table cellspacing="0" style="margin-bottom: 15px; font-size: 12px; font-family: sans-serif; text-align: center; color: #aaa"><tr><td>&nbsp;</td>';
		for (var i = 0; i < this._data.length; i++) {
			html += '<th><div style="text-align: left; position:absolute; transform: rotate(-45deg) translateX(5%); transform-origin: 0% 0%">' + this._data[i].brand + '</div></th>';
			this._brand_index.push(this._data[i].brand);
		}
		html += '</tr>';
		for (var i = 0; i < this._data.length; i++) {
			html += '<tr>';
			html += '<th style="text-align:right">' + this._data[i].brand + '</th>';
			for (var j = 0; j < this._data.length; j++) {
				value = (this._data[i].brands.hasOwnProperty(this._brand_index[j])) ? this._data[i].brands[this._brand_index[j]] : 0;
				percent = this.getPercent(value, boundaries.max, boundaries.min);
				color = (i == j) ? '#ffffff' : this.getColor(percent);
				label = (i == j) ? 'X' : '&nbsp;';
				html += '<td style="width: 50px; height: 50px; border: 1px solid #eee; background-color: ' + color + '" class="clipper-charts-detractorspromotechart-cell" data-brand-i="' + i + '" data-brand-j="' + j + '">' + label + '</td>';
			}
			html += '</tr>';
		}
		html += '</table></div>';

		html += '<div style="clear:both"></div>';

		wrapper.innerHTML = html;

		var cells = wrapper.getElementsByClassName('clipper-charts-detractorspromotechart-cell');
		if (cells) {
			for (var i = 0; i < cells.length; i++) {
				cells[i].addEventListener('touchstart', this.hnd_touch.bind(this));
				cells[i].addEventListener('touchmove', this.hnd_touch.bind(this));
				cells[i].addEventListener('touchend', this.hnd_touch.bind(this));
				cells[i].addEventListener('click', this.hnd_click.bind(this));
			}
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
				width: 220,
				height: 200,
				className: 'clipper-charts-promvsdetrpromotechart-brand'
			}
		};

		if (settings) {
			this.settings = clipper._merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this.draw();
	};
	clipper.charts.PromVsDetrPromote_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.PromVsDetrPromote_Chart.constructor = clipper.charts.PromVsDetrPromote_Chart;

	clipper.charts.PromVsDetrPromote_Chart.prototype.draw = function() {
		document.getElementById(this.id).innerHTML = '';
		var wrapper = document.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.width = '100%';
		document.getElementById(this.id).appendChild(wrapper);
		var fHtml = '<div style="font-family:sans-serif;font-size:13px;margin-bottom:10px;color:#aaa">';
		fHtml += '	<div style="background-color:#d96d20;width:26px;height:13px;display:inline-block;"></div> Promoters';
		fHtml += '	<div style="background-color:#6dacdf;width:26px;height:13px;display:inline-block;"></div> Detractors';
		fHtml += '</div>';
		var itm = null,
			Pv = 0,
			Dv = 0;
		for (var idx = 0; idx < this._data.length; idx++) {
			itm = this._data[idx];
			Pv = ((itm.promoters * 0.85) / 2) * 100;
			Dv = ((itm.detractors * 0.85) / 2) * 100;
			Px = (itm.promoters * 40) + 10;
			Dx = (itm.detractors * -40) + 90;
			svg = '<div class="' + this.settings.brandContainer.className + '" style="position:relative;width:' + this.settings.brandContainer.width + ';height:' + this.settings.brandContainer.height + ';float:left;border: 1px solid #ccc">';
			svg += '	<h2 style="position:absolute;right: 10px;font-size:13px;font-family:sans-serif;text-align:right">' + itm.brand + '</h2>';
			svg += '	<svg width="100%" height="100%">';
			svg += '		<g>';
			svg += '			<circle cx="' + Px + '%" cy="50%" r="' + Pv + '%" fill="#d96d20" />';
			svg += '			<circle cx="' + Dx + '%" cy="50%" r="' + Dv + '%" fill="#6dacdf" />';
			svg += '		</g>';
			svg += '		<g>';
			svg += '			<text x="' + Px + '%" y="50%" font-size="16" font-family="verdana" style="fill:#333;text-anchor:middle;stroke-width:2px;stroke:#fff;stroke-opacity:0.25;">' + (itm.promoters*100) + '%</text>';
			svg += '			<text x="' + Dx + '%" y="50%" font-size="16" font-family="verdana" style="fill:#333;text-anchor:middle;stroke-width:2px;stroke:#fff;stroke-opacity:0.25;">' + (itm.detractors*100) + '%</text>';
			svg += '			<text x="50%" y="90%" font-size="16" font-family="verdana" style="fill:#333;text-anchor:middle;stroke-width:2px;stroke:#fff;stroke-opacity:0.25;">' + (itm.diff*100) + '%</text>';
			svg += '		</g>';
			svg += '	</svg>';
			svg += '</div>';
			fHtml += svg;
		}	

		fHtml += '<div style="clear:both;"></div>';	

		wrapper.innerHTML = fHtml;
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
			overlay_style = overlay.style;
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
	};
// END What brand messages are associated with Promoters, Passives and Detractors Chart

/**
 * What does my brand represent to Promoters as compared to Detractors Chart
 */
	clipper.charts.DNA_Chart = function(id, settings, data) {
		clipper.charts.Chart.call(this, id, settings, data);

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
		css = '.clipper-charts-dnachart-wrapper {' +
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
			'	padding: 1% 1% 1% 1%;' +
			'}' +
			'.clipper-charts-dnachart-promoters {' +
			'	background: #f0f6fb' +
			'}' +
			'.clipper-charts-dnachart-detractors {' +
			'	background: #fbf0e8' +
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
		if (!data.hasOwnProperty('rows')) throw 'Unexpected format.';
		var rows = data.rows;
		var f = [];
		for (i in rows) {
			f.push({
				brand: rows[i].c[0].v,
				detractors: rows[i].c[3].v / 100,
				passives: rows[i].c[2].v / 100,
				promoters: rows[i].c[1].v / 100,
				score: rows[i].c[4].v
			});
		}
		return f;
	},

	Loyalty_Chart: function(data) {
		if (!data.hasOwnProperty('rows')) throw 'Unexpected format.';
		var rows = data.rows;
		var f = [];
		for (var i = 1; i < rows.length; i++) {
			f.push({ brand: rows[i].c[0].v, loyalty: rows[i].c[1].v });
		}
		return f;
	},

	DoctorsPromote_Chart: function(data) {
		if (!data.hasOwnProperty('ds')) throw 'Unexpected format.';
		if (!data.hasOwnProperty('sa')) throw 'Unexpected format.';
		if (!data.hasOwnProperty('se')) throw 'Unexpected format.';
		if (!data.hasOwnProperty('ss')) throw 'Unexpected format.';
		var f = [{
			satisfied: {
				amount: data.sa.perc / 100,
				exclusive: {
					amount: data.se.perc / 100
				},
				shared: {
					amount: data.ss.perc / 100
				}
			},
			dissatisfied: {
				amount: data.ds.perc / 100
			}
		}];
		return f;	
	},

	PromotersPromote_Chart: function(data) {
		if (!data.hasOwnProperty('rows')) throw 'Unexpected format.';
		var rows = data.rows;
		var f = [];
		var o = null;
		for (var i = 0; i < rows.length; i++) {
			o = {};
			o.brand = rows[i].c[0].v;
			o.brands = {};
			o.brands[rows[i].c[2].v] = parseInt(rows[i].c[3].v) / 100;
			f.push(o);
		}
		return f;
	},

	DetractorsPromote_Chart: function(data) {
		var f = [];
		var o = null;
		var brandFromTitle = new RegExp('(.*?)\\sDetractors');
		var brand = '';
		for (var i = 0; i < data.length; i++) {
			if (!data[i].hasOwnProperty('rows')) throw 'Unexpected format.';
			brand = brandFromTitle.exec(data[i].title);
			if (brand !== null) brand = brand[1];
			o = {
				brand: brand,
				brands: {}
			}
			for (var j = 0; j < data[i].rows.length; j++) {
				o.brands[data[i].rows[j].c[0].v] = parseInt(data[i].rows[j].c[1].v) / 100;
			}
			f.push(o);
		}
		return f;
	},

	PromVsDetrPromote_Chart: function(data) {
		var f = [];
		var o = {};
		var brandFromTitle = new RegExp('(.*?)\\:');
			var brand = '';
		for (var i = 0; i < data.length; i++) {
			if (!data[i].hasOwnProperty('rows')) throw 'Unexpected format.';
			brand = brandFromTitle.exec(data[i].title);
			if (brand !== null) brand = brand[1];
			o = {
				brand: brand,
				promoters: parseFloat(data[i].rows[1].c[2].v) / 100,
				detractors: parseFloat(data[i].rows[0].c[2].v) / 100,
			};
			o.diff = o.detractors - o.promoters;
			f.push(o);
		}
		return f;
	},

	PPDBrandMessages_Chart: function(data) {
		if (!data.hasOwnProperty('rows')) throw 'Unexpected format.';
		var r = null;
		var f = [];
		for (var i = 0; i < data.rows.length; i++) {
			r = data.rows[i].c;
			for (var j = 0; j < data.rows.length; j++) {
				if (r[(j * 5) + 1].v !== null) {
					f.push({
						message: r[0].v,
						detractors: r[(j * 5) + 1].v / 100,
						passives: r[(j * 5) + 2].v / 100,
						promoters: r[(j * 5) + 3].v / 100,
						lcl: r[(j * 5) + 4].v / 100,
						hcl: r[(j * 5) + 5].v / 100
					});
					break;
				}
			}
		}
		return f;
	},

	DNA_Chart: function(data) {
		var f = [];
		var o = null;
		var brandFromTitle = new RegExp('What is (.*?)\\\'s');
		var b, d, p;
		for (var i = 0; i < data.length; i++) {
			o = {};
			b = brandFromTitle.exec(data[i].brand);
			o.brand = b[1];
			o.detractors = [];
			for (var j = 0; j < data[i].detractors.length; j++) {
				d = data[i].detractors[j].replace("<span>\"</span>", "");
				o.detractors.push(d);
			}
			o.promoters = [];
			for (var j = 0; j < data[i].promoters.length; j++) {
				d = data[i].promoters[j].replace("<span>\"</span>", "");
				o.promoters.push(d);
			}
			f.push(o);
		}
		return f;
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

clipper.uid = function() {
	var now = Date.now();
	var rnd = (Math.random().toString(36)+'00000000000000000').slice(2,18);
	return now + '-' + rnd;
}