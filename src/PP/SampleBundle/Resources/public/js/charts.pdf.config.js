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
    image: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCA4NjcgMTc4IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiMxQzUwODg7fQ0KCS5zdDF7ZmlsbDojOUE0NDQzO30NCjwvc3R5bGU+DQo8cG9seWdvbiBjbGFzcz0ic3QwIiBwb2ludHM9IjUsNiA1LDE0OSAzMywxNDkgMzMsOTEgMTE2LDkxIDExNiw2NCAzNCw2NCAzNCwzNSAxMzUsMzUgMTM1LDYgIi8+DQo8cmVjdCB4PSIxNjMiIHk9IjYiIGNsYXNzPSJzdDAiIHdpZHRoPSIyOCIgaGVpZ2h0PSIxNDMiLz4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0zMjkuMywxMDFoOS43YzIuMS0xLDguNi0xLjQsMTQuNC03LjFjNC44LTQuNyw2LjYtMTAuNCw2LjYtMTIuNmMwLTE3LjQsMC0zNC45LDAtNTIuM2MwLTIuNS0xLjEtOC43LTUuOS0xNC4zDQoJYy02LjgtNy45LTE2LjEtOS0xOC4xLTkuMkMyOTcsNS4zLDI1OCw1LjIsMjE5LDV2MTQ0aDI3di00OGg0NWw0MS4zLDQ4SDM3MEwzMjkuMywxMDF6IE0yNDUsNzJWMzVoODZ2MzdIMjQ1eiIvPg0KPHBhdGggY2xhc3M9InN0MCIgZD0iTTM4NSwyNnY0M2MxLDIuOCwzLjQsOC43LDguOCwxMy45YzUuMSw0LjksMTAuOCw3LjEsMTMuNiw4LjFjMzEuMSwwLDYxLjcsMCw5Mi43LDB2MjlIMzg1DQoJYzAuMywyLDIuNywyMC4zLDE2LjcsMjYuOGMyLjEsMSw0LjgsMS4yLDguMywyLjJjMzAsMCw2MCwwLDkwLDBjMS42LDAsMTEuNywwLjcsMjAuMS03LjFjNy4yLTYuOCw3LjktMTUuNiw3LjktMTcuNg0KCWMwLTEyLjgsMC0yNS42LDAtMzguM2MwLTIuMi0wLjgtOC4zLTUuOS0xNC4xQzUxNi4yLDY1LjMsNTA5LDY0LDUwNyw2NGMtMzAuNSwwLTYxLDAtOTIsMFYzNWgxMTJ2LTdjMC0xLjgtMC42LTExLTguNi0xNy40DQoJYy00LjItMy40LTguMy00LjYtMTAuOC00LjZjLTMxLjksMC02My44LDAtOTUuNywwYy0yLjMtMS05LjctMC4xLTE2LjYsNC45QzM4Ny45LDE2LjUsMzg2LDIzLjksMzg1LDI2eiIvPg0KPHBvbHlnb24gY2xhc3M9InN0MCIgcG9pbnRzPSI1NTYsNiA1NTYsMzUgNjExLDM1IDYxMSwxNDkgNjQwLDE0OSA2NDAsMzUgNjk2LDM1IDY5Niw2ICIvPg0KPHBhdGggY2xhc3M9InN0MSIgZD0iTTg1NS40LDE1LjlDODQ4LjgsNy42LDgzOS44LDYsODM3LjcsNmMtMzAuMiwwLTYwLjQsMC05MC43LDBjLTIuMS0xLTExLjItMC4yLTE4LjksNi42DQoJYy02LjcsNS45LTguMSwxMy4xLTkuMSwxNS40djk2YzEsMS45LDEuMiwxMC43LDguNCwxNy4yYzYsNS41LDEzLjEsNS44LDE1LjMsNi44YzE5LjgsMCwzOS41LDAsNTkuMiwwbDMwLDIzLjdWMTQ4DQoJYzIsMC4yLDEyLDEuMywyMC41LTYuM2M3LjUtNi43LDguNS0xNS40LDguNS0xNy4zYzAtMzEuMSwwLTYyLjIsMC05My4zQzg2MSwyOC40LDg2MC4zLDIyLjEsODU1LjQsMTUuOXogTTgzMC4xLDExOC45DQoJYy0xLjcsMS41LTMuNywxLjEtMy44LDEuMWMtMjQuMSwwLTQ4LjIsMC03Mi4yLDBjLTAuNSwwLTIuNSwwLjEtNC4yLTEuMWMtMi40LTEuNi0xLjgtNC42LTIuOC00LjlWNDBjMC0wLjMtMC4xLTIuMSwxLjQtMy44DQoJYzEuMi0xLjMsMy4yLTIuMiw1LTEuMmMyNC4yLDAsNDguNCwwLDcyLjYsMGMxLjYtMSwzLjItMC42LDQuNCwwLjZjMS4xLDEuMiwxLjYsMi42LDAuNiw0LjJjMCwyNC44LDAsNDkuNSwwLDc0LjINCglDODMyLDExNS43LDgzMS40LDExNy44LDgzMC4xLDExOC45eiIvPg0KPC9zdmc+DQo=',
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
