/* charts.pdf.config.js */

/* jshint quotmark: single */

// Charts style variables
var color1 = '#CC6633',
  color2 = '#08488c',
  heatmapHot = '#CC6633',
  heatmapCold = '#ffffff',
  strokeColor = '#cccccc',
  fillColor ='#4f4f4f',
  textColor = '#333333',
  white = '#ffffff',
  font = 'raleway',
  bubbleColor = '#919191',
  bubbleMeanColor = '#ff2a1a',
  logoSettings = {
    image: 'data:image/png;base64,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',
    width: '50px',
    height: '15px',
    opacity: 0.5,
    position: 'bottom right'
  };

function hexToRgb(hex) {
  var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? [
    parseInt(result[1], 16),
    parseInt(result[2], 16),
    parseInt(result[3], 16)
  ] : null;
}


var ChartsSettings = {
  settings: {
    'NPS_Chart': {
      detractorsBar: {
        fill: color1,
        strokeWidth: 0,
        strokeOpacity: 0,
        strokeColor: strokeColor
      },
      passivesBar: {
        fill: white,
        strokeWidth: 0,
        strokeOpacity: 0,
        strokeColor: white
      },
      promotersBar: {
        fill: color2,
        strokeWidth: 0,
        strokeOpacity: 0,
        strokeColor: strokeColor
      },
      textColor: textColor,
      textFont: font,
      logo: logoSettings
    },
    'Loyalty_Chart': {
      bubbles: {
        opacity: 0.8,
        strokeColor: white
      },
      brandBubble: {
        fill: bubbleColor
      },
      meanBubble: {
        fill: bubbleMeanColor,
      },
      textColor: textColor,
      textFont: font,
      textWeight: '600',
      logo: logoSettings
    },
    'DoctorsPromote_Chart': {
      allDoctors: {
        fill: fillColor,
        color: white
      },
      dissatisfied: {
        fill: color1,
        color: white
      },
      satisfied: {
        fill: color2,
        color: white
      },
      exclusive: {
        fill: color2,
        color: white
      },
      shared: {
        fill: color2,
        color: white
      },
      textFont: font,
      logo: logoSettings
    },
    'PromotersPromoteMean_Chart': {
      bubbles: {
        opacity: 0.8,
        strokeColor: white
      },
      brandBubble: {
        fill: bubbleColor
      },
      meanBubble: {
        fill: bubbleMeanColor,
      },
      textColor: textColor,
      textFont: font,
      textWeight: '600',
      logo: logoSettings
    },
    'PromotersPromote_Chart': {
      heatmap: {
        lowerColor:  hexToRgb(heatmapCold), // R, G, B
        higherColor:  hexToRgb(heatmapHot), // R, G, B
      },
      textColor: textColor,
      textFont: font,
      logo: logoSettings,
      legendAlignment: 'center'
    },
    'DetractorsPromote_Chart': {
      heatmap: {
        lowerColor:  hexToRgb(heatmapCold), // R, G, B
        higherColor:  hexToRgb(heatmapHot), // R, G, B
      },
      labelTextColor: textColor,
      textColor: textColor,
      textFont: font,
      logo: logoSettings,
      legendAlignment: 'center'
    },
    'PromVsDetrPromote_Chart': {
      brandContainer: {
        width: '46%',
        height: '200px',
        border: '1px solid #f0f0f0',
        margin: '1%'
      },
      bubbles: {
        fontWeight: '700',
        //textShadow: '1px 1px 2px rgba(0,0,0,0.5), -1px 0px rgba(0,0,0,1), 0 0 4px rgba(0,0,0,0.3)'
        textShadow: 'none'
      },
      detractorsBubble: {
        fill: color1,
        textColor: white
      },
      promotersBubble: {
        fill: color2,
        textColor: white
      },
      difference: {
        textColor: textColor,
        fontWeight: '700'
      },
      textColor: textColor,
      textFont: font,
      logo: logoSettings
    },
    'PPDBrandMessages_Chart': {
      heatmap: {
        lowerColor:  hexToRgb(heatmapCold), // R, G, B
        higherColor:  hexToRgb(heatmapHot), // R, G, B
      },
      textColor: textColor,
      textFont: font,
      logo: logoSettings
    },
    'DNA_Chart': {
      promotersSection: {
        textColor: color2,
        image: 'data:image/svg+xml;charset=utf-8,%3C?xml%20version=%221.0%22?%3E%0A%3Csvg%20width=%2223.999999999999996%22%20height=%2223.999999999999996%22%20xmlns=%22http://www.w3.org/2000/svg%22%20xmlns:svg=%22http://www.w3.org/2000/svg%22%3E%0A%20%3Cg%3E%0A%20%20%3Ctitle%3ELayer%201%3C/title%3E%0A%20%20%3Cpath%20fill=%22%2308488c%22%20d=%22m0.644043,17.683128l3.158647,0l0,-9.475942l-3.158647,0l0,9.475942zm17.372559,-8.68628c0,-0.868629%20-0.710695,-1.579323%20-1.579323,-1.579323l-4.982765,0l0.750177,-3.608754l0.023689,-0.252692c0,-0.323761%20-0.134241,-0.623833%20-0.34745,-0.837042l-0.837042,-0.829145l-5.195974,5.203871c-0.292175,0.284278%20-0.4659,0.679109%20-0.4659,1.113423l0,7.896619c0,0.868628%200.710695,1.579323%201.579324,1.579323l7.106956,0c0.65542,0%201.21608,-0.394831%201.452978,-0.963388l2.384779,-5.567115c0.07107,-0.181623%200.110552,-0.37114%200.110552,-0.576453l0,-1.508254l-0.007896,-0.007896l0.007896,-0.063173z%22%20id=%22svg_1%22/%3E%0A%20%3C/g%3E%0A%3C/svg%3E',
        backgroundColor: 'transparent'
      },
      detractorsSection: {
        textColor: color1,
        image: 'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20width=%2224%22%20height=%2224%22%20fill=%22%23CC6633%22%20stroke=%22none%22%3E%0A%20%20%3Cpath%20d=%22m12.961337,6.802268l-7.689431,0c-0.709136,0%20-1.315747,0.427191%20-1.572062,1.042345l-2.580231,6.023388c-0.076894,0.196507%20-0.119613,0.401559%20-0.119613,0.623698l0,1.631868l0.008544,0.008543l-0.008544,0.068352c0,0.939819%200.768943,1.708763%201.708763,1.708763l5.391145,0l-0.811663,3.90452l-0.025631,0.273403c0,0.350296%200.145245,0.674961%200.375928,0.905643l0.905644,0.8971l5.630372,-5.630371c0.307577,-0.307579%200.495541,-0.734768%200.495541,-1.204679l0,-8.543812c0,-0.939819%20-0.768943,-1.708762%20-1.708762,-1.708762zm3.417523,0l0,10.252574l3.417526,0l0,-10.252574l-3.417526,0z%22/%3E%0A%3C/svg%3E',
        backgroundColor: 'transparent'
      },
      textFont: font,
      logo: logoSettings,
      animation: {
        easing: 'cubicInOut',
        duration: 250,
        framerate: 50
      }
    }
  },
  
};
