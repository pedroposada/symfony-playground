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
	clipper.charts.Chart = function(DOMContainer, settings, data) {
		// Check dependencies
		if (typeof google == 'undefined' && typeof google.visualization == 'undefined') {
			throw 'Google Visualization API must be loaded before creating charts.';
		}

		if (clipper.charts.tools.isDOMelement(DOMContainer)) {
			this.container = DOMContainer;
		} else if (typeof DOMContainer === 'string' && document.getElementById(DOMContainer) !== null) {
			this.container = document.getElementById(DOMContainer);
		} else {
			throw 'Providen DOMContainer is not a valid HTMLElement. DOMContainer typeof is ' + (typeof DOMContainer);
		}

		// Add class to the wrapper
		var wrapper = this.container,
			type = this.type;
		if (wrapper) {
			var machineName = type.replace('_', '');
			machineName = machineName.toLowerCase();
			clipper.charts.tools.injectClass('clipper-charts-' + machineName, wrapper);
		}

		var defaultSettings = {
			logo: {
				image: 'none',
				width: '0px',
				height: '0px',
				opacity: 0.3,
				position: 'bottom right'
			},
			copyright: {
				opacity: 1,
				fontSize: 12,
				fontWeight: 500,
				fontColor: '#333333',
				fontFamily: 'sans-serif',
				position: 'bottom left'
			},
			textWeight_brand: 600,
			textWeight_labels : 500
		};
		if (settings) {
			this.settings = clipper.charts.tools.merge(settings, defaultSettings);
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

	clipper.charts.Chart.prototype.hasLogo = function() {
		return (this.settings.logo.image !== 'none');
	}

	clipper.charts.Chart.prototype.getLogo = function() {
		var me = this.container;
		var logo = me.ownerDocument.createElement('div');
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
	};

	clipper.charts.Chart.prototype.hasCopyrightNotice = function () {
		return !(this.settings.copyright.position.indexOf('none') > -1);
	}

	clipper.charts.Chart.prototype.getCopyrightNotice = function () {
		var me = this.container;
		var notice = me.ownerDocument.createElement('div');
		notice.className = 'copyright-notice';
		var y = new Date();
		y = y.getFullYear();
		notice.appendChild(me.ownerDocument.createTextNode('© Copyright ' + y + ' Doctor\'s Guide Publishing Limited.'));
		notice.style.position = 'absolute';
		notice.style.opacity = this.settings.copyright.opacity;
		notice.style.filter = 'alpha(opacity=' + (parseFloat(this.settings.copyright.opacity) * 100) + ')';
		notice.style.fontFamily = this.settings.copyright.fontFamily;
		notice.style.fontSize = this.settings.copyright.fontSize + 'px';
		notice.style.fontWeight = this.settings.copyright.fontWeight;
		notice.style.color = this.settings.copyright.color;
		if (this.settings.copyright.position.indexOf('top') > -1) {
			notice.style.top = '10px';
		} else {
			notice.style.bottom = '10px';
		}
		if (this.settings.copyright.position.indexOf('left') > -1) {
			notice.style.left = '10px';
		} else {
			notice.style.right = '10px';
		}
		return notice;
	};

	clipper.charts.Chart.prototype.getTooltip = function() {
		var machineName = this.type.replace('_', '');
			machineName = machineName.toLowerCase();

		var doc = this.container.ownerDocument;
		var tt = doc.createElement('div');
		tt.className = 'clipper-charts-' + machineName + '-tooltip'
		tt.style.position = 'absolute';
		tt.style.top = '0px';
		tt.style.left = '0px';
		tt.style.display = 'none';
		tt.style.backgroundColor = '#ffffff';
		tt.style.padding = '0.25em 0.5em';
		tt.style.fontSize = '14px';
		tt.style.fontWeight = '500';
		if (tt.style.hasOwnProperty('boxShadow')) tt.style.boxShadow = '0px 2px 5px 1px rgba(51,51,51,0.4)';
		if (tt.style.hasOwnProperty('webkitBoxShadow')) tt.style.webkitBoxShadow = '0px 2px 5px 1px rgba(51,51,51,0.4)';
		if (tt.style.hasOwnProperty('mozBoxShadow')) tt.style.mozBoxShadow = '0px 2px 5px 1px rgba(51,51,51,0.4)';
		var tto = doc.createElement('div');
		tto.className = 'clipper-charts-' + machineName + '-tooltip-over';
		tto.style.display = 'none';
		tto.style.position = 'absolute';
		tto.style.width = '100%';
		tto.style.height = '100%';
		tto.style.top = '0px';
		tto.style.left = '0px';

		return {
			tooltip: tt,
			overlay: tto
		};
	};

	clipper.charts.Chart.prototype.setTooltipListeners = function(btns) {
		var machineName = this.type.replace('_', '');
			machineName = machineName.toLowerCase();

		for (var bi = 0; bi < btns.length; bi++) {
			var b = btns[bi];
			var self = this;
			b.addEventListener('touchstart', function (e) {
				var tt = self.container.getElementsByClassName('clipper-charts-' + machineName+ '-tooltip')[0];
				var tto = self.container.getElementsByClassName('clipper-charts-' + machineName + '-tooltip-over')[0];
				if (this.getAttribute('data-tooltip-content')){
					tt.innerHTML = this.getAttribute('data-tooltip-content');
				} else {
					tt.innerHTML = this.innerHTML;
				}
				tto.style.display = 'block';
				tt.style.display = 'inline';
				var x = e.clientX || e.touches[0].clientX,
					y = e.clientY || e.touches[0].clientY,
					tx = (window.scrollX - self.container.offsetLeft - self.container.offsetParent.offsetLeft) + x,
					ty = (window.scrollY - self.container.offsetTop - self.container.offsetParent.offsetTop) + y;
				tt.style.left = tx + 'px';
				tt.style.top = ty + 'px';
			});
		}
		var tto = this.container.getElementsByClassName('clipper-charts-' + machineName + '-tooltip-over')[0];
		tto.addEventListener('touchstart', function (e) {
			var tt = self.container.getElementsByClassName('clipper-charts-' + machineName + '-tooltip')[0];
			tt.style.display = 'none';
			this.style.display = 'none';
		});
	};

	clipper.charts.Chart.prototype.getEmptyNotice = function() {
		var wrapper = this.container.ownerDocument.createElement('div'),
			el = this.container.ownerDocument.createElement('div'),
			ws = wrapper.style,
			els = el.style,
			tel = this.container.ownerDocument.createTextNode('Not enough data');
		ws.position = 'relative';
		ws.width = '100%';
		ws.height = '100%';
		wrapper.appendChild(el);
		el.appendChild(tel);
		els.position = 'absolute';
		els.top = '0px';
		els.left = '0px';
		els.padding = '1em';
		els.color = '#fc0';
		return wrapper;
	}

	// Abstract method
	clipper.charts.Chart.prototype.draw = function() { throw 'Draw method must be overriden by child class.'; };
// END Chart Base object

clipper.charts.factory = function(type, DOMContainer, settings, data) {
	if (!clipper.charts.hasOwnProperty(type)) throw 'Chart type "' + type + '" does not exist.';

	if (settings !== null && settings.hasOwnProperty('formatter')) {
		if (!clipper.charts.formatters.hasOwnProperty(settings.formatter)) throw 'Chart data formatter "' + setting.formatter + '" does not exist.';
		data = clipper.charts.formatters[settings.formatter](data);
	}

	return new clipper.charts[type](DOMContainer, settings, data);
};
/**
 * NPS Chart
 */
	clipper.charts.NPS_Chart = function(DOMContainer, settings, data) {
		this.type = 'NPS_Chart';
		clipper.charts.Chart.call(this, DOMContainer, settings, data);

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
			scoreBars: {
				positive: {
					fill: '#cddcab'
				},
				negative: {
					fill: '#e2a8a5'
				},
				zero: {
					fill: '#7f7f7f'
				}
			},
			textColor: '#aaa',
			textFont: 'sans-serif'
		};

		defaultSettings = clipper.charts.tools.merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper.charts.tools.merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this.draw();
	};
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
		}
		return {
			min: min * -1,
			max: max
		};
	};

	clipper.charts.NPS_Chart.prototype.getPercentage = function(value, max) {
		return Math.round((value / max) * 100);
	};

	clipper.charts.NPS_Chart.prototype.getBoundaries = function() {
		var dmax = 0,
			pmax = 0;

		for (var i = 0; i < this._data.length; i++) {
			var b = this._data[i];
			if (b.detractors > dmax) dmax = b.detractors;
			if (b.promoters > pmax) pmax = b.promoters;
		}

		return {
			promoters: pmax,
			detractors: dmax
		};
	};

	clipper.charts.NPS_Chart.prototype.drawBar = function(value, max, height, setting, outsideLabel) {
		max *= 1.05; // Add 5% padding.
		var outsideLabel = (outsideLabel) ? outsideLabel : 'left';
			bheight = height * 0.7, // Use 70% of the cell for the bar
			percentage = this.getPercentage(value, max),
			showOutside = !(percentage > 10); // 10% is the min. width for inner label.

		bheight -= setting.strokeWidth * 2; // Substract the bar border.

		var html = '';
		var label = (showOutside) ? '&nbsp;' : Math.round(value * 100) + '%';
		if (showOutside && outsideLabel === 'left') {
			html += '<div style="display: inline-block; height: ' + bheight + 'px; line-height: ' + bheight + 'px; margin: 0px 10px 0px 10px; color: ' + setting.fill + '; font-weight: ' + this.settings.textWeight_brand + '">' + Math.round(value * 100) + '%</div>';
		}
		html += '<div style="display: inline-block; text-align: center; height:' + bheight + 'px; line-height:' + bheight + 'px; width:' + percentage + '%; background-color:' + setting.fill + '; color: #ffffff; font-weight: ' + this.settings.textWeight_brand + '; border: ' + setting.strokeWidth + 'px solid ' + setting.strokeColor + ';">' + label + '</div>';
		if (showOutside && outsideLabel === 'right') {
			html += '<div style="display: inline-block; height: ' + bheight + 'px; line-height: ' + bheight + 'px; margin: 0px 10px 0px 10px; color: ' + setting.fill + '; font-weight: ' + this.settings.textWeight_brand + '">' + Math.round(value * 100) + '%</div>';
		}

		return html;
	};

	clipper.charts.NPS_Chart.prototype.drawScoreBar = function(value, height) {
		var bheight = height * 0.7, // Use only 70% of the height
			bwidth = (value !== 0) ? Math.round(Math.abs(value) / 2) : 1,
			showOutside = !(bwidth > 10),
			bgc = '', // Bar color. Depends on value.
			left = '', // Bar left position. Depends on value.
			labelPos = ''; // Label position. Depends on value.

		if (value === 0) {
			bgc = this.settings.scoreBars.zero.fill;
			left = '50%';
			labelPos = 'left: 55%';
		} else if (value > 0) {
			bgc = this.settings.scoreBars.positive.fill;
			left = '50%';
			labelPos = 'left: ' + (55 + bwidth) + '%';
		} else if (value < 0) {
			bgc = this.settings.scoreBars.negative.fill;
			left = (50 - bwidth) + '%';
			labelPos = 'right: ' + (55 + bwidth) + '%';
		}

		var html = '';

		html += '<div style="margin-left: 24%; width:52%; height:100%; position:relative; font-weight:'+this.settings.textWeight_brand+'">';
		html += '<div style="position:absolute;'+labelPos+';height:'+bheight+'px;line-height:'+bheight+'px;width:35px;">' + value + '</div>';
		html += '<div style="position:absolute;height: ' + bheight + 'px; width: ' + bwidth + '%; background-color: ' + bgc + '; left: ' + left + '">';
		html += '</div>';

		return html;
	};

	clipper.charts.NPS_Chart.prototype.draw = function() {
		var me = this.container;
		me.innerHTML = '';

		if (this._data.length === 0) {
			var en = this.getEmptyNotice();
			this.container.appendChild(en);
			return;
		}

		var wrapper = me.ownerDocument.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.height = '100%';
		wrapper.style.width = '100%';

		me.appendChild(wrapper);

		var cHeight = me.clientHeight;
		var cWidth = me.clientWidth;
		var cellSpacing = 0; // Cell spacing is set to 0 to show the vertical black lines with no gaps.
		var cellHeight = (this.container.clientHeight > 0) ? Math.round(((cHeight - (cellSpacing * (this._data.length + 2)) - 50) / this._data.length)) : 33; // Calculate the cell height based on the brand number and the available height.
		if (cellHeight < 33) cellHeight = 33; // Never use a value smaller than 23 for the cellHeight or the text will be overflowed.
		var fontSize = 14;
		var boundaries = this.getBoundaries();

		// Draw chart
		var html = '';

		html += '<table border="0" width="98%" cellspacing="'+cellSpacing+'px" cellpadding="0" style="margin-left: 1%; table-layout:fixed; margin-bottom: 30px; font-size:' + fontSize + 'px; font-family:' + this.settings.textFont + '"><tr style="height:30px">';
		html += '<th width="20%">&nbsp;</th>';
		html += '<th width="23%" style="font-size:' + (fontSize-1) + 'px;font-weight:' + this.settings.textWeight_labels + '" align="center">Detractors</th>';
		html += '<th width="14%" style="font-size:' + (fontSize-1) + 'px;font-weight:' + this.settings.textWeight_labels + '" align="center">Passives</th>';
		html += '<th width="23%" style="font-size:' + (fontSize-1) + 'px;font-weight:' + this.settings.textWeight_labels + '" align="center">Promoters</th>';
		html += '<th width="20%" style="font-size:' + (fontSize-1) + 'px;font-weight:' + this.settings.textWeight_labels + '" align="center">Score</th>';
		html += '</tr>';

		for (var i = 0; i < this._data.length; i++) {
			var b = this._data[i];
			// Brand label
			html += '<tr style="height:'+cellHeight+'px"><td style="font-weight:' + this.settings.textWeight_brand + '" align="right" title="' + b.brand + '"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="clipper-charts-npschart-hastooltip">' + b.brand + '</div></td>';

			// Detractors bar
			html += '<td align="right" style="border-right: 1px solid #333;">' + this.drawBar(b.detractors, boundaries.detractors, cellHeight, this.settings.detractorsBar) + '</td>';

			// Passives bar
			html += '<td align="center" style="font-weight:' + this.settings.textWeight_brand + '">' + Math.round(b.passives * 100) + '%</td>';

			// Promoters bar
			html += '<td style="border-left: 1px solid #333;">' + this.drawBar(b.promoters, boundaries.promoters, cellHeight, this.settings.promotersBar, 'right') + '</td>';

			// Score bars
			html += '<td style="height:'+cellHeight+'px">' + this.drawScoreBar(b.score, cellHeight) + '</td>';

			html += '</tr>';
		}

		html += '</table>';

		wrapper.innerHTML = html;

		// Tooltip
		var tooltip = this.getTooltip();
		wrapper.appendChild(tooltip.tooltip);
		wrapper.appendChild(tooltip.overlay);
		var btns = wrapper.getElementsByClassName('clipper-charts-npschart-hastooltip');
		this.setTooltipListeners(btns);

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

		// Copyright notice
		if (this.settings.copyright.position.indexOf('none') === -1) {
			var copyright = this.getCopyrightNotice();
			wrapper.appendChild(copyright);
		}

	};
// END NPS Chart
/**
 * How loyal are doctors to my brand Chart
 */
	clipper.charts.Loyalty_Chart = function(DOMContainer, settings, data) {
		this.type = 'Loyalty_Chart';
		clipper.charts.Chart.call(this, DOMContainer, settings, data);

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
			boundaries: {
				mode: 'manual', // (manual | auto)
				marginMin: 0,
				marginMax: 1,
				min: 1,
				max: 4
			},
			textColor: '#aaa',
			textFont: 'sans-serif',
			textWeight: 'normal',
			textWeight_mean: 900,
			verticalAdjustment: true
		};

		defaultSettings = clipper.charts.tools.merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper.charts.tools.merge(settings, defaultSettings);
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
		}

		min = max;

		for (var i = 0; i < this._data.brands.length; i++) {
			var cv = this._data.brands[i].loyalty;
			if (cv < min) min = cv;
		}

		if (this.settings.boundaries.mode === 'manual') {
			var ret = {
				min: this.settings.boundaries.min - this.settings.boundaries.marginMin,
				max: this.settings.boundaries.max + this.settings.boundaries.marginMax
			};
			if (min < this.settings.boundaries.min) {
				// console.warn('Loyalty_Chart: The min value set is higher than the lowest value in the DataTable. Using this value instead.');
				ret.min = min - this.settings.boundaries.marginMin;
			}
			if (max > this.settings.boundaries.max) {
				// console.warn('Loyalty_Chart: The max value set is lower than the highest value in the DataTable. Using this value instead.');
				ret.max = max + this.settings.boundaries.marginMax;
			}
			return ret;
		}

		return {
			min: min - this.settings.boundaries.marginMin,
			max: max + this.settings.boundaries.marginMax
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
		var me = this.container;
		me.innerHTML = '';

		if (!this._data.hasOwnProperty('brands') || this._data.brands.length === 0) {
			var en = this.getEmptyNotice();
			this.container.appendChild(en);
			return;
		}

		var height = null;
		var bottomHeight = 120; // Pixels left for bottom ticks and labels

		// Adjust height
		if (this.settings.verticalAdjustment) {
			height = (this._data.brands.length) * 40 * 2;
			me.style.minHeight = height + 'px';
		}

		// Set font size
		var containerRect = me.getClientRects()[0];
		var fontSize = 14;
		// if (containerRect.width <= 375) fontSize = '11';
		// if (containerRect.width > 375 && containerRect.width <= 568) fontSize = '12';
		// if (containerRect.width > 568 && containerRect.width <= 667) fontSize = '13';
		// if (containerRect.width > 667 && containerRect.width <= 700) fontSize = '13';
		// if (containerRect.width > 700 && containerRect.width <= 1024) fontSize = '13';

		this._gchart = new google.visualization.BubbleChart(me);

		var brands = this._data.brands;

		// If portrait, 60%; if not, 70%
		var ww = window.innerWidth, wh = window.innerHeight;
		var chartWidth = (wh > ww) ? '60%' : '70%';
		var chartLeft = (wh > ww) ? '20%' : '15%';

		var boundaries = this.getBoundaries();
		var max = Math.round(boundaries.max);
		var min = Math.round(boundaries.min);
		var ticks = [];
		for (var t = min; t <= max; t++) {
			ticks.push(t);
		}

		var chartHeightPercentage = (height === null || height === 0) ? '75%' : ( 100 - (bottomHeight / height) * 100) + '%';

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
			titlePosition: 'none',
			sizeAxis: {
				maxSize: 11
			},
			tooltip: {
				trigger: 'none'
			},
			bubble: {
				opacity: this.settings.bubbles.opacity,
				stroke: this.settings.bubbles.strokeColor
			},
			chartArea: {
				top: 0,
				left: chartLeft,
				right: '10%',
				height: chartHeightPercentage,
				width: chartWidth
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
		var wrapper = me.querySelector('div:first-child');
		//wrapper.style.marginTop = "-8%";
		me.style.overflow = "hidden";
		var overlay = null;
		var overlay_values = null;
		for (var idx = 0; idx < data.length; idx++) {
			var overlay = me.ownerDocument.createElement('div');
			overlay.className = 'clipper-charts-loyaltychart-hastooltip';
			var overlay_style = overlay.style;
			overlay_style.textAlign = 'right';
			overlay_style.width = (chartArea.left - 15) + "px";
			overlay_style.left = "10px";
			overlay_style.top = Math.floor(cli.getYLocation((data.length - idx))) - 9 + "px";
			overlay_style.position = 'absolute';
			overlay_style.color = this.settings.textColor;
			overlay_style.fontFamily = this.settings.textFont;
			overlay_style.fontSize = fontSize + 'px';
			if (data[idx].brand === 'Mean') {
				overlay_style.fontWeight = this.settings.textWeight_mean;
			} else {
				overlay_style.fontWeight = this.settings.textWeight_brand;	
			}
			overlay_style.whiteSpace = 'nowrap';
			overlay_style.overflow = 'hidden';
			overlay_style.textOverflow = 'ellipsis';
			var overlay_text = me.ownerDocument.createTextNode(data[idx].brand);
			overlay.appendChild(overlay_text);
			wrapper.appendChild(overlay);

			overlay_values = me.ownerDocument.createElement('div');
			overlay_values.style.position = 'absolute';
			overlay_values.style.color = this.settings.textColor;
			overlay_values.style.fontFamily = this.settings.textFont;
			overlay_values.style.fontSize = fontSize + 'px';
			if (data[idx].brand === 'Mean') {
				overlay_values.style.fontWeight = this.settings.textWeight_mean;
			} else {
				overlay_values.style.fontWeight = this.settings.textWeight_brand;
			}
			overlay_values.style.left = chartArea.width + chartArea.left + 10 + 'px';
			overlay_values.style.top = cli.getBoundingBox('vAxis#0#gridline#' + (data.length - idx)).top - 9 + 'px';
			var txt = data[idx].loyalty.toString();
			if (txt.indexOf('.') === -1) {
				txt += '.00';
			}
			if (txt.indexOf('.') > txt.length - 3) {
				for (var i = 0; i < txt.indexOf('.') - (txt.length-3); i++) {
					txt += '0';
				}
			} else if (txt.indexOf('.') < txt.length - 3) {
				txt = txt.substring(0, txt.indexOf('.') + 3);
			}
			overlay_text = me.ownerDocument.createTextNode(txt);
			overlay_values.appendChild(overlay_text);
			wrapper.appendChild(overlay_values);
		}

		// Create tick lines
		for (var i = 0; i < ticks.length; i++) {
			overlay = me.ownerDocument.createElement('div');
			overlay_style = overlay.style;
			overlay_style.position = 'absolute';
			overlay_style.left = cli.getBoundingBox('hAxis#0#gridline#' + i).left + "px";
			overlay_style.top = chartArea.top + chartArea.height - 15 + "px";
			overlay_style.width = '1px';
			overlay_style.height = '15px';
			overlay_style.backgroundColor = '#333';
			wrapper.appendChild(overlay);
		}

		// Create low label
		overlay = me.ownerDocument.createElement('div');
		overlay_style = overlay.style;
		overlay_style.position = 'absolute';
		overlay_style.left = (cli.getBoundingBox('hAxis#0#gridline#0').left - 8) + "px";
		overlay_style.top = (chartArea.top + chartArea.height + 25) + "px";
		overlay_style.width = '30px';
		overlay_style.height = '15px';
		overlay_style.color = this.settings.textColor;
		overlay_style.fontFamily = this.settings.textFont;
		overlay_style.fontSize = (fontSize - 1) + 'px';
		// overlay_style.fontWeight = this.settings.textWeight;
		overlay_style.fontWeight = this.settings.textWeight_labels;
		overlay_text = me.ownerDocument.createTextNode('low');
		overlay.appendChild(overlay_text);
		wrapper.appendChild(overlay);
		// Create high label
		overlay = me.ownerDocument.createElement('div');
		overlay_style = overlay.style;
		overlay_style.position = 'absolute';
		overlay_style.left = (cli.getBoundingBox('hAxis#0#gridline#' + (max - min)).left - 12) + "px";
		overlay_style.top = (chartArea.top + chartArea.height + 25) + "px";
		overlay_style.width = '30px';
		overlay_style.height = '15px';
		overlay_style.color = this.settings.textColor;
		overlay_style.fontFamily = this.settings.textFont;
		overlay_style.fontSize = (fontSize - 1) + 'px';
		// overlay_style.fontWeight = this.settings.textWeight;
		overlay_style.fontWeight = this.settings.textWeight_labels;
		overlay_text = me.ownerDocument.createTextNode('high');
		overlay.appendChild(overlay_text);
		wrapper.appendChild(overlay);

		// Create "loyalty score" label
		overlay = me.ownerDocument.createElement('div');
		overlay_style = overlay.style;
		overlay_style.position = 'absolute';
		overlay_style.left = (Math.floor(chartArea.width / 2) - 50 + chartArea.left) + "px";
		overlay_style.top = (chartArea.top + chartArea.height + 50) + "px";
		overlay_style.width = '100px';
		overlay_style.height = '15px';
		overlay_style.color = this.settings.textColor;
		overlay_style.lineHeight = '20px';
		overlay_style.fontFamily = this.settings.textFont;
		overlay_style.fontSize = (fontSize - 1) + 'px';
		overlay_style.textAlign = 'center';
		// overlay_style.fontWeight = this.settings.textWeight;
		overlay_style.fontWeight = this.settings.textWeight_labels;
		overlay_text = me.ownerDocument.createTextNode('Loyalty score');
		overlay.appendChild(overlay_text);
		wrapper.appendChild(overlay);

		// Delete top line
		overlay = me.ownerDocument.createElement('div');
		overlay_style = overlay.style;
		overlay_style.position = 'absolute';
		overlay_style.left = chartArea.left + 'px';
		overlay_style.top = chartArea.top + 'px';
		overlay_style.height = '5px';
		overlay_style.width = chartArea.width + 'px';
		overlay_style.backgroundColor = '#ffffff';
		wrapper.appendChild(overlay);

		// Tooltip
		var tooltip = this.getTooltip();
		wrapper.appendChild(tooltip.tooltip);
		wrapper.appendChild(tooltip.overlay);
		var btns = wrapper.getElementsByClassName('clipper-charts-loyaltychart-hastooltip');
		this.setTooltipListeners(btns);

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

		// Copyright notice
		if (this.settings.copyright.position.indexOf('none') === -1) {
			var copyright = this.getCopyrightNotice();
			wrapper.appendChild(copyright);
		}

	};
// END How loyal are doctors to my brand Chart
/**
 * How many brands does a doctor promote Chart
 */
	clipper.charts.DoctorsPromote_Chart = function(DOMContainer, settings, data) {
		this.type = 'DoctorsPromote_Chart';
		clipper.charts.Chart.call(this, DOMContainer, settings, data);

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

		defaultSettings = clipper.charts.tools.merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper.charts.tools.merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this.draw();
	};
	clipper.charts.DoctorsPromote_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.DoctorsPromote_Chart.constructor = clipper.charts.DoctorsPromote_Chart;

	clipper.charts.DoctorsPromote_Chart.prototype.draw = function() {
		var me = this.container;
		me.innerHTML = '';

		if (!this._data.hasOwnProperty('satisfied') || !this._data.hasOwnProperty('dissatisfied')) {
			var en = this.getEmptyNotice();
			this.container.appendChild(en);
			return;
		}

		this._gchart = new google.visualization.OrgChart(me);

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
		dt.setRowProperty(0, 'style', 'padding: 0.25em 0.5em; font-family: ' + this.settings.textFont + '; font-weight: 700; color: ' + this.settings.allDoctors.color + '; background: ' + this.settings.allDoctors.fill + '; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Dissatisfied', f:'<big style="font-size:17px;font-weight:bold">' + Math.round(this._data.dissatisfied.amount * 100) + '%</big><br><strong style="font-size:17px">Dissatisfied</strong><br>(0 brands promoted)'}, 'All Doctors', this._data.dissatisfied.amount ]);
		dt.setRowProperty(1, 'style', 'padding: 0.25em 0.5em; font-family: ' + this.settings.textFont + '; font-weight: normal; color: ' + this.settings.dissatisfied.color + '; background: ' + this.settings.dissatisfied.fill + '; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Satisfied', f:'<big style="font-size:17px;font-weight:bold">' + Math.round(this._data.satisfied.amount * 100) + '%</big><br><strong style="font-size:17px">Satisfied</strong><br>(&gt;0 brands promoted)'}, 'All Doctors', this._data.satisfied.amount ]);
		dt.setRowProperty(2, 'style', 'padding: 0.25em 0.5em; font-family: ' + this.settings.textFont + '; font-weight: normal; color: ' + this.settings.satisfied.color + '; background: ' + this.settings.satisfied.fill + '; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Exclusive', f:'<big style="font-size:17px;font-weight:bold">' + Math.round(this._data.satisfied.exclusive.amount * 100) + '%</big><br><strong style="font-size:17px">Exclusive</strong><br>(1 brand promoted)'}, 'Satisfied', this._data.satisfied.exclusive.amount ]);
		dt.setRowProperty(3, 'style', 'padding: 0.25em 0.5em; font-family: ' + this.settings.textFont + '; font-weight: normal; color: ' + this.settings.exclusive.color + '; background: ' + this.settings.exclusive.fill + '; border: 0px; box-shadow: none');
		dt.addRow([ {v:'Shared', f:'<big style="font-size:17px;font-weight:bold">' + Math.round(this._data.satisfied.shared.amount * 100) + '%</big><br><strong style="font-size:17px">Shared</strong><br>(&gt;1 brand promoted)<br>'}, 'Satisfied', this._data.satisfied.shared.amount ]);
		dt.setRowProperty(4, 'style', 'padding: 0.25em 0.5em; font-family: ' + this.settings.textFont + '; font-weight: normal; color: ' + this.settings.shared.color + '; background: ' + this.settings.shared.fill + '; border: 0px; box-shadow: none');

		this._gchart.draw(dt, options);

		var wrapper = me;

		// Adjust position and size
		var table = wrapper.getElementsByClassName('google-visualization-orgchart-table')[0];
		if (table.clientWidth > wrapper.clientWidth) {
			var ratio = wrapper.clientWidth / table.clientWidth;
			table.style.transform = 'scale(' + ratio + ',' + ratio + ')';
		}
		if (wrapper.clientHeight > table.clientHeight) {
			table.style.paddingTop = Math.floor((wrapper.clientHeight - table.clientHeight) / 2) + 'px';
		}

		// Logo
		if (this.settings.logo.image !== 'none') {
			wrapper.style.position = 'relative';
			var logo = this.getLogo();
			wrapper.appendChild(logo);
			table.style.paddingBottom = (logo.clientHeight + 5) + 'px';
		}

		// Copyright notice
		if (this.settings.copyright.position.indexOf('none') === -1) {
			var copyright = this.getCopyrightNotice();
			wrapper.appendChild(copyright);
		}

	};
// END How many brands does a doctor promote Chart
/**
 * Heatmap base class
 */
 	clipper.charts.Heatmap_Chart = function(DOMContainer, settings, data) {
 		clipper.charts.Chart.call(this, DOMContainer, settings, data);

		var defaultSettings = {
			valueType: 'absolute',
			showLabels: true,
			heatmap: {
				lowerColor: [255, 255, 255], // R, G, B
				higherColor: [0, 0, 0] // R, G, B
			},
			textColor: '#aaa',
			textFont: 'sans-serif',
			legendAlignment: 'auto'
		};

		defaultSettings = clipper.charts.tools.merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper.charts.tools.merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		// Add class to the wrapper
		var wrapper = this.container;
		if (wrapper) {
			clipper.charts.tools.injectClass('clipper-charts-heatmap', wrapper);
		}
 	};
 	clipper.charts.Heatmap_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.Heatmap_Chart.constructor = clipper.charts.Heatmap_Chart;

	clipper.charts.Heatmap_Chart.prototype.getBoundaries = function() {
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
		};
	};

	clipper.charts.Heatmap_Chart.prototype.getPercent = function(value, max, min) {
		if (value === null) return null;
		min = min || 0;
		var v = value - min;
		var M = max - min;
		return (v / M);
	};

	clipper.charts.Heatmap_Chart.prototype.getColorHex = function(percent, min, max) {
		var val = Math.abs(Math.floor(min + ((max - min) * percent)));
		var hex = val.toString(16);
		if (hex.length < 2) hex = '0' + hex;
		return hex;
	};

	clipper.charts.Heatmap_Chart.prototype.getColor = function(percent) {
		if (percent === null) percent = 0;

		var heatmap = this.settings.heatmap;

		var r = this.getColorHex(percent, heatmap.lowerColor[0], heatmap.higherColor[0]);
		var g = this.getColorHex(percent, heatmap.lowerColor[1], heatmap.higherColor[1]);
		var b = this.getColorHex(percent, heatmap.lowerColor[2], heatmap.higherColor[2]);

		return '#' + r + g + b;
	};

	clipper.charts.Heatmap_Chart.prototype.getCellTextColor = function(percent) {
		var heatmap = this.settings.heatmap;

		var rv = Math.abs(Math.floor(heatmap.lowerColor[0] + ((heatmap.higherColor[0] - heatmap.lowerColor[0]) * percent))),
			gv = Math.abs(Math.floor(heatmap.lowerColor[1] + ((heatmap.higherColor[1] - heatmap.lowerColor[1]) * percent))),
			bv = Math.abs(Math.floor(heatmap.lowerColor[2] + ((heatmap.higherColor[2] - heatmap.lowerColor[2]) * percent)));

		// Perceptive luminance
		var rl = (rv * 0.299), // red: 29.9%
			gl = (gv * 0.587), // green: 58.7%
			bl = (gv * 0.114), // blue: 11.4%
			tl = rl + gl + bl; // Total luminance (0-255)

		var al = 1 - (tl / 255); // Absolute luminance (0 - 1)

		// This is where we can set our boundary. 50% is the intuitive limit for
		// dark / bright change of text color, but I found out that 40% is actually
		// better.
		return (al > 0.4) ? '#f9f9f9' : '#333333';
	};

	clipper.charts.Heatmap_Chart.prototype.getLegend = function(orientation) {
		var o = orientation || 'vertical';
		var machineName = this.type.replace('_', '');
			machineName = machineName.toLowerCase();
		var colorH = this.getColor(1),
			colorL = this.getColor(0);
		var ts = (!Date.now) ? new Date().getTime() : Date.now();
		if (orientation === 'vertical') {
			html = '<svg width="75" height="200" class="clipper-charts-' + machineName + '-legend-svg">';
			html += '<defs><linearGradient id="clipper-charts-heatmap-gradient-' + ts + '" x1="50%" x2="50%" y1="0%" y2="100%"><stop stop-color="' + colorL + '" offset="0" /><stop stop-color="' + colorH + '" offset="1" /></linearGradient></defs>';
			html += '<g><rect height="90%" width="45%" x="0%" y="7%" fill="url(#clipper-charts-heatmap-gradient-'+ ts +')"/></g>';
			html += '<g>';
			for (var i = 0; i <= 100; i = i + 20) {
				var y = (i * 0.88) + 10;
				html += '<text font-size="13px" font-weight="600" x="55%" y="' + y + '%">' + i + '%</text>';
			}
			html += '</g>';
			html += '</svg>';
		} else {
			html = '<svg width="200" height="75" class="clipper-charts-' + machineName + '-legend-svg">';
			html += '<defs><linearGradient id="clipper-charts-heatmap-gradient-' + ts + '" x1="0%" y1="50%" x2="100%" y2="50%"><stop stop-color="' + colorL + '" offset="0" /><stop stop-color="' + colorH + '" offset="1" /></linearGradient></defs>';
			html += '<g><rect width="83%" height="50%" y="0%" x="5%" fill="url(#clipper-charts-heatmap-gradient-'+ ts +')"/></g>';
			html += '<g>';
			for (var i = 0; i <= 100; i = i + 20) {
				var y = (i * 0.83) + 5;
				html += '<text text-anchor="middle" font-weight="600" font-size="13px" y="75%" x="' + y + '%">' + i + '%</text>';
			}
			html += '</g>';
			html += '</svg>';
		}

		return html;

		// Old table saved just in case
		// <table style="position:absolute;font-family: ' + this.settings.textFont + '; font-size: ' + fontSize + 'px; text-align: center" cellspacing="2" cellpadding="0">';
		// for (var i = 0; i <= 10; i = i + 2) {
		// 	html += '<tr>';
		// 	color = this.getColor(i / 10);
		// 	border = (i == 0) ? 'border:1px solid #eee;' : 'border:1px solid ' + color + ';';
		// 	html += '<td style="width: 30px;"><div style="width:30px;height:30px;background-color: ' + color + ';' + border +'"></div></td>';
		// 	html += '<td style="width: 30px; height: 30px; font-weight:500;">' + (i * 10) + '%</td>';
		// 	html += '</tr>';
		// }
		// html += '</tr></table>';
	};

	// Deprecated. Doesn't work
	// clipper.charts.Heatmap_Chart.prototype.draw_note = function(brand, value, x, y) {
	// 	var note = this.container.ownerDocument.createElement('div');
	// 	if (this.settings.valueType === 'absolute') {
	// 		value = (value * 100) + '%';
	// 	}
	// 	note.id = this.id + '-note';
	// 	note.style.position = 'absolute';
	// 	note.style.left = x - 65 + 'px';
	// 	note.style.top = y - 70 + 'px';
	// 	var noteSVG = '<svg width="112" height="57" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg">';
	// 	noteSVG += '<g>';
	// 	noteSVG += '  <g>';
	// 	noteSVG += '   <path stroke="null" fill="#cccccc" fill-opacity="0.4" stroke-width="0" d="m1.93805,31.00885a0.64602,0.64602 0 0 1 -0.64602,-0.64602l0,-28.42478a0.64602,0.64602 0 0 1 0.64602,-0.64602l70.41595,0a0.64602,0.64602 0 0 1 0.64602,0.64602l0,28.42478a0.64602,0.64602 0 0 1 -0.64602,0.64602l-35.53098,0l8.39824,8.39823l-16.79647,-8.39823l-26.48675,0l0.00001,0z"/>';
	// 	noteSVG += '<path stroke="null" fill="#cccccc" fill-opacity="0.6" stroke-width="0" d="m1.29204,30.36283a0.64602,0.64602 0 0 1 -0.64602,-0.64602l0,-28.42478a0.64602,0.64602 0 0 1 0.64602,-0.64602l70.41595,0a0.64602,0.64602 0 0 1 0.64602,0.64602l0,28.42478a0.64602,0.64602 0 0 1 -0.64602,0.64602l-35.53098,0l8.39823,8.39823l-16.79646,-8.39823l-26.48674,0z"/>';
	// 	noteSVG += '    <path stroke="#cccccc" fill="#ffffff" d="m0.64602,29.71682a0.64602,0.64602 0 0 1 -0.64602,-0.64602l0,-28.42478a0.64602,0.64602 0 0 1 0.64602,-0.64602l70.41595,0a0.64602,0.64602 0 0 1 0.64602,0.64602l0,28.42478a0.64602,0.64602 0 0 1 -0.64602,0.64602l-35.53098,0l8.39823,8.39823l-16.79646,-8.39823l-26.48674,0z"/>';
	// 	noteSVG += '   <g>';
	// 	noteSVG += '    <text fill="#000000" stroke-width="0" font-weight="bold" font-size="13" font-family="Arial" y="18.55" x="7.500001" text-anchor="start">' + value + '</text>';
	// 	noteSVG += '   </g>';
	// 	noteSVG += '  </g>';
	// 	noteSVG += ' </g>';
	// 	noteSVG += '</svg>';
	// 	note.innerHTML = noteSVG;
	// 	return note;
	// };

	// Deprecated. Doesn't work
	// clipper.charts.Heatmap_Chart.prototype.hnd_touch = function(e) {
	// 	var me = this.container;
	// 	var self = e.target;
	// 	var idx = self.getAttribute('data-brand-i');
	// 	var j = self.getAttribute('data-brand-j');
	// 	var cStatus = (this._data[idx].hasOwnProperty('touchStatus')) ? this._data[idx].touchStatus : '';
	// 	if (cStatus == 'touchstart' && e.type == 'touchend') {
	// 		var brand = this._brand_index[idx];
	// 		var value = (this._data[idx].competitors.hasOwnProperty(this._brand_index[j])) ? this._data[idx].competitors[this._brand_index[j]] : 0;
	// 		if (value === 0) return;
	// 		var wrapper = me.getElementsByTagName('div')[0];
	// 		var x = Math.floor(e.changedTouches[0].clientX) - wrapper.getBoundingClientRect().left;
	// 		var y = Math.floor(e.changedTouches[0].clientY) - wrapper.getBoundingClientRect().top;
	// 		var oldNote = document.getElementById(this.id + '-note');
	// 		if (oldNote) {
	// 			oldNote.parentNode.removeChild(oldNote);
	// 		}
	// 		wrapper.appendChild(this.draw_note(brand, value, x, y));
	// 	}
	// 	this._data[idx].touchStatus = e.type;
	// };

	// Deprecated. Doesn't work.
	// clipper.charts.Heatmap_Chart.prototype.hnd_mouse = function(e) {
	// 	if (e.type == 'mouseleave') {
	// 		var oldNote = document.getElementById(this.id + '-note');
	// 		if (oldNote) {
	// 			oldNote.parentNode.removeChild(oldNote);
	// 		}
	// 	}
	// 	if (e.type == 'mouseenter') {
	// 		var self = e.target;
	// 		var idx = self.getAttribute('data-brand-i');
	// 		var j = self.getAttribute('data-brand-j');
	// 		var brand = this._brand_index[idx];
	// 		var value = (this._data[idx].competitors.hasOwnProperty(this._brand_index[j])) ? this._data[idx].competitors[this._brand_index[j]] : 0;
	// 		if (value === 0) return;
	// 		var wrapper = document.getElementById(this.id).getElementsByTagName('div')[0];
	// 		var x = Math.floor(e.clientX) - wrapper.getBoundingClientRect().left;
	// 		var y = Math.floor(e.clientY) - wrapper.getBoundingClientRect().top;
	// 		var oldNote = document.getElementById(this.id + '-note');
	// 		if (oldNote) {
	// 			oldNote.parentNode.removeChild(oldNote);
	// 		}
	// 		wrapper.appendChild(this.draw_note(brand, value, x, y));
	// 	}
	// };

// END Heatmap base class
/**
 * Amongst my Promoters, how many other brands do they promote 
 *  and which other brand is most promoted Chart
 */
	clipper.charts.PromotersPromote_Chart = function(DOMContainer, settings, data) {
		this.type = 'PromotersPromote_Chart';
		clipper.charts.Heatmap_Chart.call(this, DOMContainer, settings, data);

		this._brand_index = [];

		this.draw();
	};
	clipper.charts.PromotersPromote_Chart.prototype = Object.create(clipper.charts.Heatmap_Chart.prototype);
	clipper.charts.PromotersPromote_Chart.constructor = clipper.charts.PromotersPromote_Chart;

	clipper.charts.PromotersPromote_Chart.prototype.draw = function() {
		var me = this.container;
		me.innerHTML = '';

		if (this._data.length === 0) {
			var en = this.getEmptyNotice();
			this.container.appendChild(en);
			return;
		}

		var wrapper = me.ownerDocument.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.height = '100%';
		wrapper.style.width = '100%';

		me.appendChild(wrapper);

		// Set font size
		var containerRect = me.getClientRects()[0];
		var fontSize = 14;
		// if (containerRect.width <= 375) fontSize = 11;
		// if (containerRect.width > 375 && containerRect.width <= 568) fontSize = 12;
		// if (containerRect.width > 568 && containerRect.width <= 667) fontSize = 13;
		// if (containerRect.width > 667 && containerRect.width <= 700) fontSize = 13;
		// if (containerRect.width > 700 && containerRect.width <= 1024) fontSize = 13;

		this._brand_index = [];
		var value = 0;
		var boundaries = this.getBoundaries();
		var color = '';
		var textColor = '';
		var percent = '';
		var border = '';

		var html = '';

		var overflow = 'auto';

		html += '<div style="text-align:center;width:98%;padding:1%;font-size:' + (fontSize - 1) + 'px;font-family:' + this.settings.textFont + ';font-weight:'+this.settings.textWeight_labels+';line-height:1.5em">Also most commonly promote...</div>';

		html += '<div style="margin-top:0px;margin-bottom:35px;max-width:79%;overflow-x: ' + overflow + '; ">';
		
		html += '<table cellspacing="0" style="table-layout:fixed;margin-left: auto;margin-right:auto; margin-bottom: 15px; font-size: ' + fontSize + 'px; font-family: ' + this.settings.textFont + '; text-align: center; color: ' + this.settings.textColor + '">';

		// html += '<tr><th colspan="' + (this._data.length + 2) + '" style="padding:5px;">Promoted brand</th></tr>';

		html += '<tr><td>&nbsp;</td><td>&nbsp;</td>';

		var brandtext = '';
		for (var i = 0; i < this._data.length; i++) {
			brandtext = this._data[i].brand;
			brandtext = (brandtext.length < 13) ? brandtext : brandtext.substring(0, 10) + '...';
			html += '<th align="center"><div title="' + this._data[i].brand + '" class="clipper-charts-promoterspromotechart-hastooltip" data-tooltip-content="' + this._data[i].brand + '" style="width:40px"><svg width="20" height="80"><g><text fill="' + this.settings.textColor + '" font-size="' + fontSize + '" font-family="' + this.settings.textFont + '" font-weight="'+this.settings.textWeight_brand+'" text-anchor="left" x="14" y="80" transform="rotate(-90 14,80)">' + brandtext + '</text></g></svg></div></th>';
			this._brand_index.push(this._data[i].brand);
		}
		html += '</tr>';

		var label = '';
		
		for (var i = 0; i < this._data.length; i++) {
			html += '<tr>';
			if (i === 0) {
				html += '<td rowspan="' + (this._data.length + 1) + '"><svg width="20" height="180"><g><text fill="' + this.settings.textColor + '" stroke-width="0" x="50%" y="50%" font-size="' + (fontSize - 1)+ '" font-family="' + this.settings.textFont + '" font-weight="'+this.settings.textWeight_labels+'" text-anchor="middle" transform="rotate(-90 10,90) ">Promoters of these brands...</text></g></svg></td>';
			}
			html += '<th style="font-weight: '+this.settings.textWeight_brand+'; text-align:right; padding: 5px 5px 5px 0px; line-height: 18px" title="' + this._data[i].brand + '"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:100px" class="clipper-charts-promoterspromotechart-hastooltip" data-tooltip-content="' + this._data[i].brand + '">' + this._data[i].brand + '</div></th>';
			for (var j = 0; j < this._data.length; j++) {
				value = (this._data[i].competitors.hasOwnProperty(this._brand_index[j])) ? this._data[i].competitors[this._brand_index[j]] : null;
				percent = this.getPercent(value, boundaries.max, boundaries.min);
				color = this.getColor(percent);
				// txtColor = this.getCellTextColor(percent);
				txtColor = '#333333';
				label = (this.settings.showLabels && percent !== null) ? Math.round(percent * 100) + '%' : '&nbsp;';
				html += '<td style="font-weight: 600; font-size: 13px; width: 50px; height: 50px; border: 1px solid #eee; color: ' + txtColor + '; background-color: ' + color + '" class="clipper-charts-promoterspromotechart-cell" data-brand-i="' + i + '" data-brand-j="' + j + '">' + label + '</td>';
			}
			html += '</tr>';}

			html += '</table></div>';

		// Legend
		html += '<div class="clipper-charts-promoterspromotechart-legend" style="position:absolute;top:0px;right:0px;margin-left: 5%; width:20%;height:100%">';

		html += this.getLegend('vertical');
		
		html += '</div>'

		html += '<div style="clear:both"></div>';

		wrapper.innerHTML = html;

		// Tooltip
		var tooltip = this.getTooltip();
		wrapper.appendChild(tooltip.tooltip);
		wrapper.appendChild(tooltip.overlay);
		var btns = wrapper.getElementsByClassName('clipper-charts-promoterspromotechart-hastooltip');
		this.setTooltipListeners(btns);

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

		// Copyright notice
		if (this.settings.copyright.position.indexOf('none') === -1) {
			var copyright = this.getCopyrightNotice();
			wrapper.appendChild(copyright);
		}

		// var cells = wrapper.getElementsByClassName('clipper-charts-promoterspromotechart-cell');
		// if (cells) {
		// 	for (var i = 0; i < cells.length; i++) {
		// 		cells[i].addEventListener('touchstart', this.hnd_touch.bind(this));
		// 		cells[i].addEventListener('touchmove', this.hnd_touch.bind(this));
		// 		cells[i].addEventListener('touchend', this.hnd_touch.bind(this));
		// 		cells[i].addEventListener('mouseenter', this.hnd_mouse.bind(this));
		// 		cells[i].addEventListener('mouseleave', this.hnd_mouse.bind(this));
		// 	}
		// }

		var tables = wrapper.getElementsByTagName('table');
		var tbl = tables[0];
		var tblh = tbl.clientHeight;
		var legend = wrapper.getElementsByClassName('clipper-charts-promoterspromotechart-legend-svg')[0];
		var lh = legend.clientHeight;
		legend.style.position = 'absolute';
		var la = this.settings.legendAlignment;
		if (la === 'auto') {
			if (wrapper.clientHeight <= window.innerHeight && tblh > lh) {
				la = 'center';
			} else {
				la = 'bottom';
			}
		}
		if (la === 'center') {
			var rows = tbl.querySelectorAll('tr');
			var gh = tblh - (rows[0].clientHeight + rows[1].clientHeight);
			var ltop = Math.floor((gh / 2) - (lh / 2));
			legend.style.top = (rows[0].clientHeight + rows[1].clientHeight + ltop) + 'px';
		} else if (la === 'bottom') {
			legend.style.bottom = '50px';
		}
	};
// END Amongst my Promoters, how many other brands do they promote and which other brand is most promoted Chart
/**
 * Amongst doctors promoting my brand, how many other brands do they also promote
 */
	clipper.charts.PromotersPromoteMean_Chart = function(DOMContainer, settings, data) {
		this.type = 'PromotersPromoteMean_Chart';
		clipper.charts.Chart.call(this, DOMContainer, settings, data);

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
			boundaries: {
				mode: 'manual', // (manual | auto)
				marginMin: 0,
				marginMax: 1,
				min: 0,
				max: 4
			},
			textColor: '#aaa',
			textFont: 'sans-serif',
			textWeight: 'normal',
			textWeight_mean: 900,
			verticalAdjustment: true
		};

		defaultSettings = clipper.charts.tools.merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper.charts.tools.merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		this.draw();
	};
	clipper.charts.PromotersPromoteMean_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.PromotersPromoteMean_Chart.constructor = clipper.charts.PromotersPromoteMean_Chart;

	clipper.charts.PromotersPromoteMean_Chart.prototype.getBoundaries = function() {
		var min = 0,
			max = 0;

		var brands = [];
		for (var b in this._data.brands) {
			var tmp = this._data.brands[b];
			tmp.brand = b;
			brands.push(tmp);
		}

		for (var i = 0; i < brands.length; i++) {
			var cv = brands[i].mean;
			if (cv > max) max = cv;
		}

		min = max;

		for (var i = 0; i < brands.length; i++) {
			var cv = brands[i].mean;
			if (cv < min) min = cv;
		}

		if (this.settings.boundaries.mode === 'manual') {
			var ret = {
				min: this.settings.boundaries.min - this.settings.boundaries.marginMin,
				max: this.settings.boundaries.max + this.settings.boundaries.marginMax
			};
			if (min < this.settings.boundaries.min) {
				// console.warn('Loyalty_Chart: The min value set is higher than the lowest value in the DataTable. Using this value instead.');
				ret.min = min - this.settings.boundaries.marginMin;
			}
			if (max > this.settings.boundaries.max) {
				// console.warn('Loyalty_Chart: The max value set is lower than the highest value in the DataTable. Using this value instead.');
				ret.max = max + this.settings.boundaries.marginMax;
			}
			return ret;
		}

		return {
			min: min - this.settings.boundaries.marginMin,
			max: max + this.settings.boundaries.marginMax
		};
	};

	clipper.charts.PromotersPromoteMean_Chart.prototype.getMean = function() {
		if (this._data.hasOwnProperty('overall') && this._data.overall.hasOwnProperty('mean')) return this._data.overall.mean;
		if (this._data.brands.length < 1) return 0;
		var total = 0,
			len = 0;
		for (var b in this._data.brands) {
			var tmp = this._data.brands[b];
			total += tmp.mean;
			len++;
		}
		return total / len;
	};

	clipper.charts.PromotersPromoteMean_Chart.prototype.draw = function() {
		var me = this.container;
		me.innerHTML = '';

		var brands = [];
		for (var b in this._data.brands) {
			var tmp = this._data.brands[b];
			tmp.brand = b;
			brands.push(tmp);
		}

		if (brands.length === 0) {
			var en = this.getEmptyNotice();
			this.container.appendChild(en);
			return;
		}

		// Set font size
		var containerRect = me.getClientRects()[0];
		var fontSize = 14;

		this._gchart = new google.visualization.BubbleChart(me);

		// Adjust height
		var height = null;
		var bottomHeight = 120; // Pixels left for bottom ticks and labels

		if (this.settings.verticalAdjustment) {
			height = (brands.length) * 40 * 2;
			me.style.minHeight = height + 'px';
		}

		// If portrait, 60%; if not, 70%
		var ww = window.innerWidth, wh = window.innerHeight;
		var chartWidth = (wh > ww) ? '60%' : '65%';
		var chartLeft = (wh > ww) ? '25%' : '20%';

		var boundaries = this.getBoundaries();
		var max = Math.round(boundaries.max);
		var min = Math.round(boundaries.min);
		var ticks = [];
		for (var t = min; t <= max; t++) {
			ticks.push(t);
		}

		var chartHeightPercentage = (height === null || height === 0) ? '75%' : ( 100 - (bottomHeight / height) * 100) + '%';

		var options = {
			colors: [this.settings.brandBubble.fill,this.settings.meanBubble.fill],
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
			titlePosition: 'none',
			sizeAxis: {
				maxSize: 11
			},
			tooltip: {
				trigger: 'none'
			},
			bubble: {
				opacity: this.settings.bubbles.opacity,
				stroke: this.settings.bubbles.strokeColor
			},
			chartArea: {
				top: 0,
				left: chartLeft,
				right: '10%',
				height: chartHeightPercentage,
				width: chartWidth
			}
		};

		// Create Data Table.
		var dt = new google.visualization.DataTable({
			cols: [
				{ id: 'brand', label: 'brand', type: 'string' },
				{ id: 'mean', label: 'mean', type: 'number' },
				{ id: 'count', label: 'count', type: 'number' },
				{ type: 'string', role: 'style' },
			]
		});

		var data = Object.create(brands);
		var mean = this.getMean();
		data.push({
			brand: 'Mean',
			mean: parseFloat(mean.toFixed(2))
		});

		data.sort(function(a, b) {
			return a.mean - b.mean;
		});

		for (var idx = 0; idx < data.length; idx++) {
			// Defaults.
			if (!data[idx].brand) { data[idx].brand = 'undefined'; }
			if (!data[idx].mean) { data[idx].mean = 0; }

			if (data[idx].brand == 'Mean') {
				dt.addRow([
					'',
					data[idx].mean,
					(data.length - idx),
					'color: ' + this.settings.meanBubble.fill + ';'
				]);
			} else {
				dt.addRow([
					'',
					data[idx].mean,
					(data.length - idx),
					'color: ' + this.settings.brandBubble.fill + ';'
				]);
			}
		}
		

		this._gchart.draw(dt, options);

		// Create Score labels.
		var cli = this._gchart.getChartLayoutInterface();
		var chartArea = cli.getChartAreaBoundingBox();
		var wrapper = me.querySelector('div:first-child');
		//wrapper.style.marginTop = "-8%";
		me.style.overflow = "hidden";
		var overlay = null;
		var overlay_values = null;
		for (var idx = 0; idx < data.length; idx++) {
			var overlay = me.ownerDocument.createElement('div');
			overlay.className = 'clipper-charts-loyaltychart-hastooltip';
			var overlay_style = overlay.style;
			overlay_style.textAlign = 'right';
			overlay_style.width = Math.round(chartArea.left - 30) + "px";
			overlay_style.left = "30px";
			overlay_style.top = Math.floor(cli.getYLocation((data.length - idx))) - 9 + "px";
			overlay_style.position = 'absolute';
			overlay_style.color = this.settings.textColor;
			overlay_style.fontFamily = this.settings.textFont;
			overlay_style.fontSize = fontSize + 'px';
			if (data[idx].brand === 'Mean') {
				overlay_style.fontWeight = this.settings.textWeight_mean;
			} else {
				overlay_style.fontWeight = this.settings.textWeight_brand;	
			}
			overlay_style.whiteSpace = 'nowrap';
			overlay_style.overflow = 'hidden';
			overlay_style.textOverflow = 'ellipsis';
			var overlay_text = me.ownerDocument.createTextNode(data[idx].brand);
			overlay.appendChild(overlay_text);
			wrapper.appendChild(overlay);

			overlay_values = me.ownerDocument.createElement('div');
			overlay_values.style.position = 'absolute';
			overlay_values.style.color = this.settings.textColor;
			overlay_values.style.fontFamily = this.settings.textFont;
			overlay_values.style.fontSize = fontSize + 'px';
			if (data[idx].brand === 'Mean') {
				overlay_values.style.fontWeight = this.settings.textWeight_mean;
			} else {
				overlay_values.style.fontWeight = this.settings.textWeight_brand;
			}
			overlay_values.style.left = chartArea.width + chartArea.left + 10 + 'px';
			overlay_values.style.top = cli.getBoundingBox('vAxis#0#gridline#' + (data.length - idx)).top - 9 + 'px';
			var txt = data[idx].mean.toString();
			if (txt.indexOf('.') === -1) {
				txt += '.00';
			}
			if (txt.indexOf('.') > txt.length - 3) {
				for (var i = 0; i < txt.indexOf('.') - (txt.length-3); i++) {
					txt += '0';
				}
			} else if (txt.indexOf('.') < txt.length - 3) {
				txt = txt.substring(0, txt.indexOf('.') + 3);
			}
			overlay_text = me.ownerDocument.createTextNode(txt);
			overlay_values.appendChild(overlay_text);
			wrapper.appendChild(overlay_values);
		}

		// Create tick lines
		for (var i = 0; i < ticks.length; i++) {
			overlay = me.ownerDocument.createElement('div');
			overlay_style = overlay.style;
			overlay_style.position = 'absolute';
			overlay_style.left = cli.getBoundingBox('hAxis#0#gridline#' + i).left + "px";
			overlay_style.top = chartArea.top + chartArea.height - 15 + "px";
			overlay_style.width = '1px';
			overlay_style.height = '15px';
			overlay_style.backgroundColor = '#333';
			wrapper.appendChild(overlay);
		}

		// Create y axis label
		overlay = me.ownerDocument.createElement('div');
		overlay_style = overlay.style;
		overlay_style.position = 'absolute';
		overlay_style.left = "10px";
		overlay_style.top = Math.round(chartArea.top + (chartArea.height / 2) - 90) + "px";
		overlay_style.width = '20px';
		overlay_style.height = '180px';
		wrapper.appendChild(overlay);
		overlay.innerHTML = '<svg width="20" height="180"><g><text fill="' + this.settings.textColor + '" stroke-width="0" x="50%" y="50%" font-size="' + (fontSize - 1) + '" font-family="' + this.settings.textFont + '" font-weight="500" text-anchor="middle" transform="rotate(-90 10,90) ">Promoters of these brands...</text></g></svg>';

		// Create "other brands promoted" label
		overlay = me.ownerDocument.createElement('div');
		overlay_style = overlay.style;
		overlay_style.position = 'absolute';
		overlay_style.left = (Math.floor(chartArea.width / 2) - 100 + chartArea.left) + "px";
		overlay_style.top = (chartArea.top + chartArea.height + 50) + "px";
		overlay_style.width = '200px';
		overlay_style.height = '15px';
		overlay_style.color = this.settings.textColor;
		overlay_style.fontFamily = this.settings.textFont;
		overlay_style.fontSize = (fontSize - 1) + 'px';
		overlay_style.textAlign = 'center';
		// overlay_style.fontWeight = this.settings.textWeight;
		overlay_style.fontWeight = this.settings.textWeight_labels;
		overlay_style.lineHeight = '20px';
		overlay_text = me.ownerDocument.createTextNode('# of other brands promoted');
		overlay.appendChild(overlay_text);
		wrapper.appendChild(overlay);

		// Delete top line
		overlay = me.ownerDocument.createElement('div');
		overlay_style = overlay.style;
		overlay_style.position = 'absolute';
		overlay_style.left = chartArea.left + 'px';
		overlay_style.top = chartArea.top + 'px';
		overlay_style.height = '5px';
		overlay_style.width = chartArea.width + 'px';
		overlay_style.backgroundColor = '#ffffff';
		wrapper.appendChild(overlay);

		// Delete left line
		overlay = me.ownerDocument.createElement('div');
		overlay_style = overlay.style;
		overlay_style.position = 'absolute';
		overlay_style.left = chartArea.left + 'px';
		overlay_style.top = chartArea.top + 'px';
		overlay_style.width = '2px';
		overlay_style.height = (chartArea.height - 15) + 'px';
		overlay_style.backgroundColor = '#ffffff';
		wrapper.appendChild(overlay);

		// Tooltip
		var tooltip = this.getTooltip();
		wrapper.appendChild(tooltip.tooltip);
		wrapper.appendChild(tooltip.overlay);
		var btns = wrapper.getElementsByClassName('clipper-charts-loyaltychart-hastooltip');
		this.setTooltipListeners(btns);

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

		// Copyright notice
		if (this.settings.copyright.position.indexOf('none') === -1) {
			var copyright = this.getCopyrightNotice();
			wrapper.appendChild(copyright);
		}

	};
// END Amongst doctors promoting my brand, how many other brands do they also promote
/**
 * Amongst my Detractors, which other brands do they promote Chart
 */
	clipper.charts.DetractorsPromote_Chart = function(DOMContainer, settings, data) {
		this.type = 'DetractorsPromote_Chart';
		clipper.charts.Heatmap_Chart.call(this, DOMContainer, settings, data);

		this._brand_index = [];

		this.draw();
	};
	clipper.charts.DetractorsPromote_Chart.prototype = Object.create(clipper.charts.Heatmap_Chart.prototype);
	clipper.charts.DetractorsPromote_Chart.constructor = clipper.charts.DetractorsPromote_Chart;

	clipper.charts.DetractorsPromote_Chart.prototype.draw = function() {
		var me = this.container;
		me.innerHTML = '';

		if (this._data.length === 0) {
			var en = this.getEmptyNotice();
			this.container.appendChild(en);
			return;
		}

		var wrapper = me.ownerDocument.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.height = '100%';
		wrapper.style.width = '100%';

		me.appendChild(wrapper);

		// Set font size
		var containerRect = me.getClientRects()[0];
		var fontSize = 14;
		// if (containerRect.width <= 375) fontSize = 11;
		// if (containerRect.width > 375 && containerRect.width <= 568) fontSize = 12;
		// if (containerRect.width > 568 && containerRect.width <= 667) fontSize = 13;
		// if (containerRect.width > 667 && containerRect.width <= 700) fontSize = 13;
		// if (containerRect.width > 700 && containerRect.width <= 1024) fontSize = 13;

		this._brand_index = [];
		var value = 0;
		var boundaries = this.getBoundaries();
		var color = '';
		var txtcolor = '';
		var percent = 0;
		var border = '';

		var html = '';

		var overflow = 'auto';

		html += '<div style="text-align:center;width:98%;padding:1%;font-size:' + (fontSize - 1) + 'px;font-family:' + this.settings.textFont + ';font-weight:'+this.settings.textWeight_labels+';line-height:1.5em">Also promote...</div>';

		html += '<div style="margin-top:0px;margin-bottom:35px;max-width:79%;overflow-x: ' + overflow + '; ">';

		html += '<table cellspacing="0" style="table-layout:fixed;margin-left:auto;margin-right:auto; margin-bottom: 15px; font-size: ' + fontSize + 'px; font-family: ' + this.settings.textFont + '; text-align: center; color: ' + this.settings.textColor + '">';

		//html += '<tr><th colspan="' + (this._data.length + 2) + '" style="padding:5px;">Promoted brand</th></tr>';

		html += '<tr><td>&nbsp;</td><td>&nbsp;</td>';

		// html += '<tr><td>&nbsp;</td><th colspan="' + this._data.length + '" style="padding:5px;">...promote these brands</th></tr>';

		var brandtext = '';
		for (var i = 0; i < this._data.length; i++) {
			brandtext = this._data[i].brand;
			brandtext = (brandtext.length < 13) ? brandtext : brandtext.substring(0, 10) + '...';
			html += '<th align="center"><div title="' + this._data[i].brand + '" class="clipper-charts-detractorspromotechart-hastooltip" data-tooltip-content="' + this._data[i].brand + '" style="width:40px"><svg width="20" height="80"><g><text fill="' + this.settings.textColor + '" font-size="' + fontSize + '" font-weight="'+this.settings.textWeight_brand+'" font-family="' + this.settings.textFont + '" text-anchor="left" x="14" y="80" transform="rotate(-90 14,80)">' + brandtext + '</text></g></svg></div></th>';
			this._brand_index.push(this._data[i].brand);
		}
		html += '</tr>';

		var label = '';

		for (var i = 0; i < this._data.length; i++) {
			html += '<tr>';
			if (i === 0) {
				html += '<td rowspan="' + (this._data.length + 2) + '"><svg width="20" height="200"><g><text fill="' + this.settings.textColor + '" stroke-width="0" x="50%" y="50%" font-size="' + (fontSize - 1)+ '" font-family="' + this.settings.textFont + '" font-weight="'+this.settings.textWeight_labels+'" text-anchor="middle" transform="rotate(-90 10,100) ">Detractors of these brands...</text></g></svg></td>';
			}
			
			html += '<th style="font-weight: '+this.settings.textWeight_brand+'; text-align:right; padding: 5px 5px 5px 0px; line-height: 18px" title="' + this._data[i].brand + '"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:100px" class="clipper-charts-detractorspromotechart-hastooltip" data-tooltip-content="' + this._data[i].brand + '">' + this._data[i].brand + '</div></th>';
			for (var j = 0; j < this._data.length; j++) {
				value = (this._data[i].competitors.hasOwnProperty(this._brand_index[j])) ? this._data[i].competitors[this._brand_index[j]] : 0;
				percent = this.getPercent(value, boundaries.max, boundaries.min);
				color = (i == j) ? '#ffffff' : this.getColor(percent);
				// txtcolor = this.getCellTextColor(percent);
				txtColor = '#333333';
				if (i == j) {
					label = 'X';
				} else {
					if (this.settings.showLabels) {
						label = Math.round(percent * 100) + '%';
					} else {
						label = '&nbsp;';
					}
				}
				html += '<td style="font-weight:600; font-size:13px; width: 50px; height: 50px; border: 1px solid #eee; background-color: ' + color + '; color: ' + txtcolor + '" class="clipper-charts-detractorspromotechart-cell" data-brand-i="' + i + '" data-brand-j="' + j + '">' + label + '</td>';
			}
			html += '</tr>';
		}
		html += '</table></div>';

		// Legend
		html += '<div class="clipper-charts-promoterspromotechart-legend" style="position:absolute;top:0px;right:0px;margin-left: 5%; width:20%;height:100%">';

		html += this.getLegend('vertical');
		
		html += '</div>'

		html += '<div style="clear:both"></div>';

		wrapper.innerHTML = html;

		// Tooltip 
		var tooltip = this.getTooltip();
		wrapper.appendChild(tooltip.tooltip);
		wrapper.appendChild(tooltip.overlay);
		var btns = wrapper.getElementsByClassName('clipper-charts-detractorspromotechart-hastooltip');
		this.setTooltipListeners(btns);

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

		// Copyright notice
		if (this.settings.copyright.position.indexOf('none') === -1) {
			var copyright = this.getCopyrightNotice();
			wrapper.appendChild(copyright);
		}

		// var cells = wrapper.getElementsByClassName('clipper-charts-detractorspromotechart-cell');
		// if (cells) {
		// 	for (var i = 0; i < cells.length; i++) {
		// 		cells[i].addEventListener('touchstart', this.hnd_touch.bind(this));
		// 		cells[i].addEventListener('touchmove', this.hnd_touch.bind(this));
		// 		cells[i].addEventListener('touchend', this.hnd_touch.bind(this));
		// 		cells[i].addEventListener('mouseenter', this.hnd_mouse.bind(this));
		// 		cells[i].addEventListener('mouseleave', this.hnd_mouse.bind(this));
		// 	}
		// }

		var tables = wrapper.getElementsByTagName('table');
		var tbl = tables[0];
		var tblh = tbl.clientHeight;
		var legend = wrapper.getElementsByClassName('clipper-charts-detractorspromotechart-legend-svg')[0];
		var lh = legend.clientHeight;
		legend.style.position = 'absolute';
		var la = this.settings.legendAlignment;
		if (la === 'auto') {
			if (wrapper.clientHeight <= window.innerHeight && tblh > lh) {
				la = 'center';
			} else {
				la = 'bottom';
			}
		}
		if (la === 'center') {
			var rows = tbl.querySelectorAll('tr');
			var gh = tblh - (rows[0].clientHeight + rows[1].clientHeight);
			var ltop = Math.floor((gh / 2) - (lh / 2));
			legend.style.top = (rows[0].clientHeight + rows[1].clientHeight + ltop) + 'px';
		} else if (la === 'bottom') {
			legend.style.bottom = '50px';
		}

	};
// END Amongst my Detractors, which other brands do they promote Chart
/**
 * How much more of my brand do Promoters use compared to Detractors Chart
 */
	clipper.charts.PromVsDetrPromote_Chart = function(DOMContainer, settings, data) {
		this.type = 'PromVsDetrPromote_Chart';
		clipper.charts.Chart.call(this, DOMContainer, settings, data);

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

		defaultSettings = clipper.charts.tools.merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper.charts.tools.merge(settings, defaultSettings);
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
		var wrapper = this.container.getElementsByTagName('div')[0];
		var dummy = this.container.ownerDocument.createElement('div');
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
		var me = this.container;
		me.innerHTML = '';

		if (this._data.length === 0) {
			var en = this.getEmptyNotice();
			this.container.appendChild(en);
			return;
		}
		
		var wrapper = me.ownerDocument.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.width = '100%';
		me.appendChild(wrapper);

		var containerRect = this.getContainerRect();
		// This calculates the minimum visible percentage value taking into
		// account 40px (the width of a XX% label).
		// 40px * 100% / 2 (is radius)
		var minPercent = (containerRect.width === 0) ? 0 : ((60 * 100) / 2) / containerRect.width;

		// Set font size
		var fontSize = 14;
		// if (containerRect.width <= 375) fontSize = 11;
		// if (containerRect.width > 375 && containerRect.width <= 568) fontSize = 12;
		// if (containerRect.width > 568 && containerRect.width <= 667) fontSize = 13;
		// if (containerRect.width > 667 && containerRect.width <= 700) fontSize = 13;
		// if (containerRect.width > 700 && containerRect.width <= 1024) fontSize = 13;

		var fHtml = '<div style="font-weight: '+this.settings.textWeight_labels+'; font-family:' + this.settings.textFont + ';font-size:' + (fontSize - 1) + 'px;margin-bottom:10px;color:' + this.settings.textColor + '">';
		fHtml += '	<div style="background-color:' + this.settings.detractorsBubble.fill + ';width:1.3em;height:1.3em;display:inline-block;border-radius:50%;-webkit-border-radius:50%;-moz-border-radius:50%;vertical-align:middle"></div> Detractors market share';
		fHtml += '	<div style="background-color:' + this.settings.promotersBubble.fill + ';width:1.3em;height:1.3em;display:inline-block;border-radius:50%;-webkit-border-radius:50%;-moz-border-radius:50%;vertical-align:middle"></div> Promoters market share';
		fHtml += '</div>';
		var itm = null,
			Dv = 0,
			Pv = 0,
			Dx = 0,
			Px = 0,
			Dfv = 0,
			Pfv = 0;
		for (var idx = 0; idx < this._data.length; idx++) {
			// This was used for maxSized rendering where maxVal = 100% on render
			// var maxValue = this.getMax(this._data[idx]),
			// 	minValue = this.getMin(this._data[idx]);
			// 	Set maxVal to 100% both on data and on render.
			var maxValue = 1;
			itm = this._data[idx];
			// If maxValue is zero, we assume the value is zero and prevent div by zero.
			// Dv = (maxValue !== 0) ? ((itm.detractors * 0.25) / maxValue) * 100 : 0;
			// Pv = (maxValue !== 0) ? ((itm.promoters * 0.25) / maxValue) * 100 : 0;
			// Dfv = (Dv > minPercent) ? Dv : minPercent;
			// Pfv = (Pv > minPercent) ? Pv : minPercent;
			
			Dv = (maxValue !== 0) ? ((50 - minPercent) * itm.detractors) + minPercent : 0;
			Dfv = Dv / 2;
			Pv = (maxValue !== 0) ? ((50 - minPercent) * itm.promoters) + minPercent : 0;
			Pfv = Pv / 2;
			Dx = 25;
			Px = 75;
			var svg = '<div class="' + this.settings.brandContainer.className + '" style="position:relative;">';
			svg += '	<h2 style="font-size:' + (fontSize + 2) + 'px;font-family:' + this.settings.textFont + ';">' + itm.brand + '</h2>';
			svg += '	<svg width="100%" height="100%">';
			svg += '		<g>';
			// if (Dv > minPercent) {
				svg += '			<circle cx="' + Dx + '%" cy="45%" r="' + Dfv + '%" fill="' + this.settings.detractorsBubble.fill + '" />';
			// }
			// if (Pv > minPercent) {
				svg += '			<circle cx="' + Px + '%" cy="45%" r="' + Pfv + '%" fill="' + this.settings.promotersBubble.fill + '" />';
			// }
			svg += '		</g>';
			svg += '		<g>';
			// var color = (Dv <= minPercent) ? this.settings.detractorsBubble.fill : this.settings.detractorsBubble.textColor;
			var color = this.settings.detractorsBubble.textColor;
			// var shadow = (Dv <= minPercent) ? 'none' : this.settings.bubbles.textShadow;
			var shadow = this.settings.bubbles.textShadow;
			svg += '			<text x="' + Dx + '%" y="48%" font-size="16" font-family="' + this.settings.textFont + '" style="fill:' + color + ';stroke-width:0;text-anchor:middle;font-weight:' + this.settings.bubbles.fontWeight + '; text-shadow: ' + shadow + '">' + Math.round((itm.detractors * 100)) + '%</text>';
			// color = (Pv <= minPercent) ? this.settings.promotersBubble.fill : this.settings.promotersBubble.textColor;
			color = this.settings.promotersBubble.textColor;
			// shadow = (Pv <= minPercent) ? 'none' : this.settings.bubbles.textShadow;
			shadow = this.settings.bubbles.textShadow;
			svg += '			<text x="' + Px + '%" y="48%" font-size="16" font-family="' + this.settings.textFont + '" style="fill:' + color + ';stroke-width:0;text-anchor:middle;font-weight:' + this.settings.bubbles.fontWeight + '; text-shadow: ' + shadow + '">' + Math.round((itm.promoters * 100)) + '%</text>';
			var diffLabel = '',
				d = Math.round((itm.diff * 100));
			if (d > 0) {
				diffLabel = '+' + d;
 			} else if (d < 0) {
 				diffLabel = d;
			} else if (d === 0) {
				diffLabel = '0';
			}
			svg += '			<text x="50%" y="93%" font-size="16" font-family="' + this.settings.textFont + '" style="fill:' + this.settings.difference.textColor + ';text-anchor:middle;stroke-width:0;font-weight:' + this.settings.difference.fontWeight + '">' + diffLabel + '%</text>';
			svg += '		</g>';
			svg += '	</svg>';
			svg += '</div>';
			fHtml += svg;
		}

		fHtml += '<div class="clipper-charts-promvsdetrpromotechart-divider" style="clear:both;"></div>';

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
					svg.setAttribute('height', Math.round(containerWidth * 0.85) + 'px');
					//containers[i].style.height = Math.round(containerWidth * 0.85) + 'px';
				}
			}
		}

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

		// Copyright notice
		if (this.settings.copyright.position.indexOf('none') === -1) {
			var copyright = this.getCopyrightNotice();
			wrapper.appendChild(copyright);
		}

		// Adjust bottom divider to hold logo and / or copyright notice
		if (this.hasLogo() || this.hasCopyrightNotice()) {
			var divider = wrapper.getElementsByClassName('clipper-charts-promvsdetrpromotechart-divider');
			if (divider.length > 0) {
				var bottomPad = 0;
				if (this.hasLogo()) {
					bottomPad = parseInt(this.settings.logo.height)
				}
				if (this.hasCopyrightNotice() && bottomPad < this.settings.copyright.fontSize) {
					bottomPad = this.settings.copyright.fontSize;
				}
				divider[0].style.paddingBottom = (bottomPad + 10) + 'px';
			}
		}

	};
// END How much more of my brand do Promoters use compared to Detractors Chart
/**
 * What brand messages are associated with Promoters, Passives and Detractors Chart
 */
	clipper.charts.PPDBrandMessages_Chart = function(DOMContainer, settings, data) {
		this.type = 'PPDBrandMessages_Chart';
		clipper.charts.Heatmap_Chart.call(this, DOMContainer, settings, data);

		this.draw();
	};
	clipper.charts.PPDBrandMessages_Chart.prototype = Object.create(clipper.charts.Heatmap_Chart.prototype);
	clipper.charts.PPDBrandMessages_Chart.constructor = clipper.charts.PPDBrandMessages_Chart;

	clipper.charts.PPDBrandMessages_Chart.prototype.draw = function() {
		var me = this.container;
		me.innerHTML = '';

		if (this._data.length === 0) {
			var en = this.getEmptyNotice();
			this.container.appendChild(en);
			return;
		}

		var wrapper = me.ownerDocument.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.height = '100%';
		wrapper.style.width = '100%';

		me.appendChild(wrapper);

		// Set font size
		var containerRect = me.getClientRects()[0];
		var fontSize = 14;
		// if (containerRect.width <= 375) fontSize = 11;
		// if (containerRect.width > 375 && containerRect.width <= 568) fontSize = 12;
		// if (containerRect.width > 568 && containerRect.width <= 667) fontSize = 13;
		// if (containerRect.width > 667 && containerRect.width <= 700) fontSize = 13;
		// if (containerRect.width > 700 && containerRect.width <= 1024) fontSize = 13;

		var value = 0;
		var boundaries = this.getBoundaries();
		var color = '';
		var txtcolor = '';
		var percent = 0;
		var label = '';
		var border = '';

		var isLandscape = (window.innerWidth > window.innerHeight) ? true : false;

		var html = '';

		var w = (isLandscape) ? '80%' : '100%';

		html += '<div class="clipper-charts-ppdbrandmessageschart-main" style="display:inline-block; width:' + w + '; min-height:100%; vertical-align:top;">';

		html += '<div style="height: 80%; width: 100%;" class="clipper-charts-ppdbrandmessageschart-body">';

		html += '<table class="clipper-charts-ppdbrandmessageschart-maintable" cellspacing="0" cellpadding="5" style="table-layout:fixed;margin-left:auto;margin-right:auto; margin-bottom: 15px; font-size: ' + fontSize + 'px; font-family: ' + this.settings.textFont + '; text-align: center; color: ' + this.settings.textColor + '" width="98%">';

		html += '<tr><th width="35%">&nbsp;</th><th style="font-weight: '+this.settings.textWeight_labels+'; font-size: ' + (fontSize - 1) + 'px">Detractors</th><th style="font-weight: '+this.settings.textWeight_labels+'; font-size: ' + (fontSize - 1) + 'px">Passives</th><th style="font-weight: '+this.settings.textWeight_labels+'; font-size: ' + (fontSize - 1) + 'px">Promoters</th></tr>';

		var m = null;
		for (var i = 0; i < this._data.length; i++) {
			m = this._data[i];
			html += '<tr>';
			html += '<th align="right" height="30px" style="position: relative;" title="' + m.message + '"><div style="line-height: 30px; font-weight: '+this.settings.textWeight_brand+'; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" class="clipper-charts-ppdbrandmessageschart-ellipsis">' + m.message + '</div></th>';

			value = m.detractors;
			label = Math.round(value * 100);
			percent = this.getPercent(value, boundaries.max, boundaries.min);
			color = this.getColor(percent);
			// txtcolor = this.getCellTextColor(percent);
			txtColor = '#333333';
			html += '<td align="center" style="font-weight: 600; font-size:13px; background-color: ' + color + '; color: ' + txtcolor + ';">' + Math.round(value * 100) + '%</td>';

			value = m.passives;
			label = (this.settings.showLabels) ? Math.round(value * 100) : '&nbsp;';
			percent = this.getPercent(value, boundaries.max, boundaries.min);
			color = this.getColor(percent);
			// txtcolor = this.getCellTextColor(percent);
			txtColor = '#333333';
			html += '<td align="center" style="font-weight: 600; font-size:13px; background-color: ' + color + '; color: ' + txtcolor + ';">' + Math.round(value * 100) + '%</td>';

			value = m.promoters;
			label = (this.settings.showLabels) ? Math.round(value * 100) : '&nbsp;';
			percent = this.getPercent(value, boundaries.max, boundaries.min);
			color = this.getColor(percent);
			// txtcolor = this.getCellTextColor(percent);
			txtColor = '#333333';
			html += '<td align="center" style="font-weight: 600; font-size:13px; background-color: ' + color + '; color: ' + txtcolor + ';">' + Math.round(value * 100) + '%</td>';
			
			html += '</tr>';
		}

		html += '</table></div>';

		// Legend
		
		var legend_margins = '';
		// if (isLandscape) {
		// 	legend_margins = 'margin-left: auto; margin-right: 1%;';
		// } else {
			legend_margins = 'margin-left: auto; margin-right: auto;';
		// }
		
		var legend_div_style = 'width:60%; height:20%; margin-left: 40%';
		// Center to 100% if width is not enough
		if (containerRect.width < 340) {
			legend_div_style = 'width:100%; height:20%; margin-left: 0%';
		}

		html += '<div class="clipper-charts-ppdbrandmessageschart-legend" style="' + legend_div_style + '"><div style="text-align:center;padding-top:1%; ' + legend_margins + '">';
		html += this.getLegend('horizontal');
		html += '</div></div>';

		// html += '<div class="clipper-charts-ppdbrandmessageschart-legend" style="' + legend_div_style + '"><table style="padding-top:1%; ' + legend_margins + 'font-family: ' + this.settings.textFont + '; font-size: ' + fontSize + 'px; text-align: center" cellspacing="2" cellpadding="0">';
		// html += '<tr>';
		// for (var i = 0; i <= 10; i = i + 2) {
		// 	color = this.getColor(i / 10);
		// 	border = (i == 0) ? 'border:1px solid #eee;' : 'border:1px solid ' + color + ';';
		// 	html += '<td style="width: 30px;"><div style="width:30px;height:30px;background-color: ' + color + ';' + border +'"></div></td>';
		// }
		// html += '</tr><tr>';
		// for (var i = 0; i <= 10; i = i + 2) {
		// 	color = this.getColor(i / 10);
		// 	html += '<td style="width: 30px; height: 30px; font-weight:500;">' + (i * 10) + '%</td>';
		// }
		// html += '</tr></table></div>';

		html += '</div>'; //main

		if (isLandscape) {
			html += '<div class="clipper-charts-ppdbrandmessageschart-side" style="position:relative; display:inline-block; width:20%; min-height:100%; vertical-align:top;">';
			html += '-- Labels --';
			html += '</div>';
		}

		// html += '<div class="clipper-charts-ppdbrandmessageschart-tooltip" style="position:absolute;top:0px;left:0px;display:none;background-color:#ffffff;padding:0.5em;font-size:0.7em;-webkit-box-shadow: 0px 2px 5px 1px rgba(51,51,51,0.4);-moz-box-shadow: 0px 2px 5px 1px rgba(51,51,51,0.4);box-shadow: 0px 2px 5px 1px rgba(51,51,51,0.4);">Tooltip with a very long content blah blah</div>';
		// html += '<div class="clipper-charts-ppdbrandmessageschart-tooltip-over" style="display:none;position:absolute;width:100%;height:100%;top:0px;left:0px;"></div>';

		wrapper.innerHTML = html;

		// Add labels
		if (isLandscape) {
			var sideHtml = '';
			var mainTable = wrapper.getElementsByClassName('clipper-charts-ppdbrandmessageschart-maintable')[0];
			var firstRow = mainTable.querySelectorAll('tr')[0];
			var legend = wrapper.getElementsByClassName('clipper-charts-ppdbrandmessageschart-legend')[0];
			var heatmapHeight = mainTable.clientHeight - firstRow.clientHeight;
			
			sideHtml += '<div style="position:relative; margin-top: ' + (firstRow.clientHeight) + 'px; height:' + heatmapHeight + 'px; width:100%;">';
			
			sideHtml += '<svg width="100%" height="100%"><g>';
			sideHtml += '<text x="50%" y="20" text-anchor="middle" fill="' + this.settings.textColor + '" font-size="' + (fontSize - 1) + '" font-family="' + this.settings.textFont + '" font-weight="'+this.settings.textWeight_labels+'">highest</text>';
			if (heatmapHeight >= 165) {
				var sideW = Math.floor(wrapper.clientWidth * 0.20);
				sideHtml += '<text x="50%" y="50%" font-size="' + (fontSize - 1) + '" font-family="' + this.settings.textFont + '" fill="' + this.settings.textColor + '" text-anchor="middle" transform="rotate(-90, ' + Math.floor(sideW / 2) + ', ' + Math.floor(heatmapHeight / 2) + ')" font-weight="'+this.settings.textWeight_labels+'">Rank of NPS Drivers</text>';
			}
			sideHtml += '<text x="50%" y="95%" text-anchor="middle" fill="' + this.settings.textColor + '" font-size="' + (fontSize - 1) + '" font-family="' + this.settings.textFont + '" font-weight="'+this.settings.textWeight_labels+'">lowest</text>';
			sideHtml += '</g></svg>';

			sideHtml += '</div>';

			wrapper.getElementsByClassName('clipper-charts-ppdbrandmessageschart-side')[0].innerHTML = sideHtml;
		}

		// Tooltip 
		var tooltip = this.getTooltip();
		wrapper.appendChild(tooltip.tooltip);
		wrapper.appendChild(tooltip.overlay);
		var btns = wrapper.getElementsByClassName('clipper-charts-ppdbrandmessageschart-ellipsis');
		this.setTooltipListeners(btns);

		// Logo
		if (this.settings.logo.image !== 'none') {
			var logo = this.getLogo();
			wrapper.appendChild(logo);
		}

		// Copyright notice
		if (this.settings.copyright.position.indexOf('none') === -1) {
			var copyright = this.getCopyrightNotice();
			wrapper.appendChild(copyright);
		}

		// Adjust manual minheight
		if (wrapper.clientHeight < me.clientHeight) {
			wrapper.style.height = me.clientHeight + 'px';
		} else {
			var body = wrapper.getElementsByClassName('clipper-charts-ppdbrandmessageschart-main')[0];
			body.style.paddingBottom = (logo.clientHeight + 10) + 'px';
		}
	};
// END What brand messages are associated with Promoters, Passives and Detractors Chart
/**
 * What does my brand represent to Promoters as compared to Detractors Chart
 */
	clipper.charts.DNA_Chart = function(DOMContainer, settings, data) {
		this.type = 'DNA_Chart';
		clipper.charts.Chart.call(this, DOMContainer, settings, data);

		var defaultSettings = {
			promotersSection: {
				textColor: '#558ed5',
				image: 'data:image/svg+xml;charset=utf-8,%3C?xml%20version=%221.0%22?%3E%0A%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20width=%2224%22%20height=%2224%22%20fill=%22%2308488c%22%20stroke=%22none%22%3E%0A%20%20%3Cpath%20d=%22M1%2021h4v-12h-4v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06l-1.06-1.05-6.58%206.59c-.37.36-.59.86-.59%201.41v10c0%201.1.9%202%202%202h9c.83%200%201.54-.5%201.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-1.91l-.01-.01.01-.08z%22/%3E%0A%3C/svg%3E',
				backgroundColor: 'transparent'
			},
			detractorsSection: {
				textColor: '#a04e4e',
				image: 'data:image/svg+xml;charset=utf-8,%3C?xml%20version=%221.0%22?%3E%0A%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20width=%2224%22%20height=%2224%22%20fill=%22%23CC6633%22%20stroke=%22none%22%3E%0A%20%20%20%20%3Cpath%20d=%22M15%203h-9c-.83%200-1.54.5-1.84%201.22l-3.02%207.05c-.09.23-.14.47-.14.73v1.91l.01.01-.01.08c0%201.1.9%202%202%202h6.31l-.95%204.57-.03.32c0%20.41.17.79.44%201.06l1.06%201.05%206.59-6.59c.36-.36.58-.86.58-1.41v-10c0-1.1-.9-2-2-2zm4%200v12h4v-12h-4z%22/%3E%0A%3C/svg%3E%0A',
				backgroundColor: 'transparent'
			},
			textFont: 'sans-serif',
			animation: {
				easing: 'cubicInOut',
				duration: 400,
				framerate: 50
			}
		};

		defaultSettings = clipper.charts.tools.merge(this.settings, defaultSettings);

		if (settings) {
			this.settings = clipper.charts.tools.merge(settings, defaultSettings);
		} else {
			this.settings = defaultSettings;
		}

		if (!clipper.charts.easing.hasOwnProperty(this.settings.animation.easing)) {
			throw 'Easing "' + this.settings.easing + '" does not exist.';
		}

		this.draw();
	};
	clipper.charts.DNA_Chart.prototype = Object.create(clipper.charts.Chart.prototype);
	clipper.charts.DNA_Chart.constructor = clipper.charts.DNA_Chart;

	clipper.charts.DNA_Chart.prototype.slideUp = function(item) {
		if (item.hasAttribute('data-animated')) return;
		if (!item.hasAttribute('data-initialHeight')) {
			item.setAttribute('data-initialHeight', item.clientHeight);
		}
		var bHeight = parseInt(item.getAttribute('data-initialHeight'));
		var eHeight = 0;
		var easingFunc = clipper.charts.easing[this.settings.animation.easing];

		var duration = this.settings.animation.duration, // Duration in milliseconds
			framerate = this.settings.animation.framerate, // Framerate in frames per second
			totalFrames = (duration / 1000) * framerate, // Total frames
			interval = duration / totalFrames, // Tween inteval in milliseconds
			currentFrame = 0; // Current frame

			var initialOverflow = item.style.overflow;
			item.style.overflow = 'hidden';
			item.setAttribute('data-animated', 'true');

			var tween = function () {
				var h = Math.floor(easingFunc(currentFrame, bHeight, eHeight, totalFrames));
				item.style.height = h + 'px';
				currentFrame++;
				if (currentFrame <= totalFrames) {
					setTimeout(tween, interval);
				} else {
					item.style.display = 'none';
					item.style.overflow = initialOverflow;
					item.removeAttribute('data-animated');
				}
			}
			tween();
	};

	clipper.charts.DNA_Chart.prototype.slideDown = function(item) {
		if (item.hasAttribute('data-animated')) return;
		var bHeight = 0;
		var eHeight = parseInt(item.getAttribute('data-initialHeight'));
		var easingFunc = clipper.charts.easing[this.settings.animation.easing];

		var duration = this.settings.animation.duration, // Duration in milliseconds
			framerate = this.settings.animation.framerate, // Framerate in frames per second
			totalFrames = (duration / 1000) * framerate, // Total frames
			interval = duration / totalFrames, // Tween inteval in milliseconds
			currentFrame = 0; // Current frame

			var initialOverflow = item.style.overflow;
			item.style.overflow = 'hidden';
			item.style.display = 'block';
			item.setAttribute('data-animated', 'true');

			var tween = function () {
				var h = Math.floor(easingFunc(currentFrame, bHeight, eHeight, totalFrames));
				item.style.height = h + 'px';
				currentFrame++;
				if (currentFrame <= totalFrames) {
					setTimeout(tween, interval);
				} else {
					item.style.overflow = initialOverflow;
					item.removeAttribute('data-animated');
				}
			}
			tween();
	};

	clipper.charts.DNA_Chart.prototype.slideToggle = function(item) {
		var h = item.clientHeight;
		if (h > 0) {
			this.slideUp(item);
		} else {
			this.slideDown(item);
		}
	};

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
			'}' +
			'.clipper-charts-dnachart-body h4 {' +
			'   padding-left: 40px;' +
			'	height: 30px;' +
			'	line-height: 30px;' +
			'}' +
			'.clipper-charts-dnachart-promoters, .clipper-charts-dnachart-detractors {' +
			'	display: inline-block;' +
			'	vertical-align: top;' +
			'	width: 48%;' +
			'	margin: 0%;' +
			'	padding: 1%;' +
			'	padding-top: 3px;' +
			'}' +
			'.clipper-charts-dnachart-promoters {' +
			'	background-color: ' + this.settings.promotersSection.backgroundColor + ';' +
			'	color: ' + this.settings.promotersSection.textColor + ';' +
			'}' +
			'.clipper-charts-dnachart-promoters h4 {' +
			'	background-image: url(' + this.settings.promotersSection.image + ');' +
			'	background-size: 30px;' +
			'	background-repeat: no-repeat;' +
			'	background-position: top left' +
			'}' +
			'.clipper-charts-dnachart-detractors {' +
			'	background-color: ' + this.settings.detractorsSection.backgroundColor + ';' +
			'	color: ' + this.settings.detractorsSection.textColor + ';' +
			'}' +
			'.clipper-charts-dnachart-detractors h4 {' +
			'	background-image: url(' + this.settings.detractorsSection.image + ');' +
			'	background-size: 30px;' +
			'	background-repeat: no-repeat;' +
			'	background-position: top left' +
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
		var me = this.container;
		me.innerHTML = '';

		if (this._data.length === 0) {
			var en = this.getEmptyNotice();
			this.container.appendChild(en);
			return;
		}

		if (me.ownerDocument.getElementById('clipper-charts-dnachart-style') === null) {
			var style = me.ownerDocument.createElement('style');
			style.id = 'clipper-charts-dnachart-style';
			style.innerHTML = this.getCSS();
			me.ownerDocument.getElementsByTagName('head')[0].appendChild(style);
		}

		var wrapper = me.ownerDocument.createElement('div');
		wrapper.style.position = 'relative';
		wrapper.style.height = '100%';

		me.appendChild(wrapper);

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
							var text = this._data[i].promoters[j];
							if (text.match(/^<br\s*\/?>$/i) === null) {
								text = '"' + text + '"';
							}
							html += '<li>' + text + '</li>';
						}
					html += '</ul>';
				html += '</div>';
			}
			if (this._data[i].detractors.length > 0) {
				html += '<div class="clipper-charts-dnachart-detractors">';
					html += '<h4>Detractors</h3>';
					html += '<ul>';
						for (var j = 0; j < this._data[i].detractors.length; j++) {
							var text = this._data[i].detractors[j];
							if (text.match(/^<br\s*\/?>$/i) === null) {
								text = '"' + text + '"';
							}
							html += '<li>' + text + '</li>';
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
			if (this.settings.logo.position.indexOf('bottom') > -1) {
				wrapper.style.paddingBottom = (logo.clientHeight + 5) + 'px';
			}
		}

		// Copyright notice
		if (this.settings.copyright.position.indexOf('none') === -1) {
			var copyright = this.getCopyrightNotice();
			wrapper.appendChild(copyright);
		}

		var titles = me.querySelectorAll('.clipper-charts-dnachart-brand > h3');
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
clipper.charts.formatters = {
	
	NPS_Chart: function(data) {
		if (!data.hasOwnProperty('length')) throw 'Unexpected format.';
		if (data.length === 0) return data;
		// Clone object
		data = JSON.parse(JSON.stringify(data));
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
		if (data.hasOwnProperty('length') && data.length === 0) {
			// No data
			return {};
		}
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
		};
	},

	PromotersPromote_Chart: function(data) {
		if (!data.hasOwnProperty('length')) throw 'Unexpected format.';
		// Clone object
		data = JSON.parse(JSON.stringify(data));
		for (var i = 0; i < data.length; i++) {
			if (data[i].competitors.hasOwnProperty('length') && data[i].competitors.length === 0) {
				data[i].competitors = {};
			}
			for (var c in data[i].competitors) {
				data[i].competitors[c] = data[i].competitors[c] / 100;
			}
		}
		return data;
	},

	PromotersPromoteMean_Chart: function(data) {
		return data;
	},

	DetractorsPromote_Chart: function(data) {
		if (!data.hasOwnProperty('length')) throw 'Unexpected format.';
		// Clone object
		data = JSON.parse(JSON.stringify(data));
		for (var i = 0; i < data.length; i++) {
			if (data[i].competitors.hasOwnProperty('length') && data[i].competitors.length === 0) {
				data[i].competitors = {};
			}
			for (var c in data[i].competitors) {
				data[i].competitors[c] = data[i].competitors[c] / 100;
			}
		}
		return data;
	},

	PromVsDetrPromote_Chart: function(data) {
		// Clone object
		data = JSON.parse(JSON.stringify(data));
		for (var i = 0; i < data.length; i++) {
			data[i].promoters = parseFloat(data[i].promoters) / 100;
			data[i].detractors = parseFloat(data[i].detractors) / 100;
			data[i].diff = parseFloat(data[i].diff) / 100;
		}
		return data;
	},

	PPDBrandMessages_Chart: function(data) {
		// Clone object
		data = JSON.parse(JSON.stringify(data));
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
clipper.charts.easing = {

	/**
	 * All functions have the same parameters.
	 * Implemented as seen on http://gizma.com/easing/ replacing c with (e - b).
	 * 
	 * @param numeric t Time (actual)
	 * @param numeric b Begining. The starting property value.
	 * @param numeric e End. The final property value.
	 * @param numeric d Duration. Total time of the animation.
	 */

	linear: function (t, b, e, d) {
		return ((e - b) * (t / d)) + b;
	},

	quadIn: function (t, b, e, d) {
		t /= d;
		return (e - b) * (t * t) + b;
	},

	quadOut: function (t, b, e, d) {
		t /= d;
		return -(e - b) * t * (t - 2) + b;
	},

	quadInOut: function (t, b, e, d) {
		t /= d / 2;
		if (t < 1) return (e - b)/2 * (t * t) + b;
		t--;
		return -(e - b)/2 * (t*(t-2) - 1) + b;
	},

	cubicIn: function (t, b, e, d) {
		t /= d;
		return (e - b) * (t * t * t) + b;
	},

	cubicOut: function (t, b, e, d) {
		t /= d/2;
		t--;
		return (e - b) * (t * t * t + 1) + b;
	},

	cubicInOut: function (t, b, e, d) {
		t /= d / 2;
		if (t < 1) return (e - b)/2 * (t * t * t) + b;
		t -= 2;
		return (e - b)/2 * (t * t * t + 2) + b;
	},

	sinusoidalIn: function (t, b, e, d) {
		return -(e - b) * Math.cos(t/d * (Math.PI/2)) + (e - b) + b;
	},

	sinusoidalOut: function (t, b, e, d) {
		return (e - b) * Math.sin(t / d * (Math.PI/2)) + b;
	},

	sinusoidalInOut: function (t, b, e, d) {
		return -(e - b)/2 * (Math.cos(Math.PI*t/d) - 1) + b;
	}

};
clipper.charts.tools = {};
/**
 * Takes an object and compares it with other object (the defaults object).
 * If the first object lacks properties from the defaults, they are created.
 * Recursive.
 * @params object obj
 *   Object to test
 * @params object defaults
 *   Default object
 */
clipper.charts.tools.merge = function(obj, defaults) {
	for (var p in defaults) {
		if (obj.hasOwnProperty(p)) {
			if (typeof defaults[p] == 'object') {
				obj[p] = clipper.charts.tools.merge(obj[p], defaults[p]);
			}
		} else {
			obj[p] = defaults[p];
		}
	}
	return obj;
};

/**
 * Injects a class name into a DOM object.
 * 
 * @param  string className
 *   Class name to inject
 * @param  object object
 *   DOM object
 */
clipper.charts.tools.injectClass = function(className, object) {
	if (object.className.indexOf(className) > -1) return;
	if (object.className === '') {
		object.className = className;
	} else {
		object.className += ' ' + className;
	}
};

/**
 * Returns a unique id
 *
 * @return string
 *   A Unique identifier
 */
clipper.charts.tools.uid = function() {
	var now = Date.now();
	var rnd = (Math.random().toString(36)+'00000000000000000').slice(2,18);
	return now + '-' + rnd;
};

/**
 * Returns whether an object is a DOM element or not. Crossbrowser.
 *
 * @param {object} Obj An object to test.
 * @return {bool} True if it is a DOM element; false otherwise.
 */
clipper.charts.tools.isDOMelement = function(obj) {
	try {
		return obj instanceof HTMLElement;
	} catch (e) {
		return (typeof obj === 'object') &&
			(obj.nodeType === 1) &&
			(typeof obj.style === 'object') &&
			(typeof obj.ownerDocument === 'object');
	}
};