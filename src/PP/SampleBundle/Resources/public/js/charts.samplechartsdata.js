var samplechartsdata = [
  {
    chartmachinename:"NPS",
    charttype: "NPS_Chart",
    drilldown: {"countries":["USA"],"specialties":["Oncology","Cardiology"],"regions":[]},
    filter: {"country":"","region":"","specialty":"","countries":[]},
    countTotal: 2,
    countFiltered: 2,
    datatable: {"cols":[{"id":"b","label":"Brand","type":"string"},{"id":"P","label":"Promoters","type":"number"},{"id":"a","label":"Passives","type":"number"},{"id":"d","label":"Detractors","type":"number"},{"id":"s","label":"Score","type":"number"}],"rows":[{"c":[{"v":"AA-123"},{"v":0,"f":"0%"},{"v":0,"f":"0%"},{"v":100,"f":"100%"},{"v":-100,"f":"-100"}],"p":{"Brand":"AA-123"}},{"c":[{"v":"BB-456"},{"v":50,"f":"50%"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":0,"f":"0"}],"p":{"Brand":"BB-456"}},{"c":[{"v":"CC-789"},{"v":0,"f":"0%"},{"v":0,"f":"0%"},{"v":100,"f":"100%"},{"v":-100,"f":"-100"}],"p":{"Brand":"CC-789"}},{"c":[{"v":"DD-123"},{"v":50,"f":"50%"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":0,"f":"0"}],"p":{"Brand":"DD-123"}},{"c":[{"v":"EE-456"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":50,"f":"50%"},{"v":-50,"f":"-50"}],"p":{"Brand":"EE-456"}},{"c":[{"v":"FF-789"},{"v":0,"f":"0%"},{"v":50,"f":"50%"},{"v":50,"f":"50%"},{"v":-50,"f":"-50"}],"p":{"Brand":"FF-789"}}]}, 
  },
  {
    chartmachinename:"Loyalty",
    charttype: "Loyalty_Chart",
    drilldown: {"countries":["USA"],"specialties":["Oncology","Cardiology"],"regions":[]},
    filter: {"country":"","region":"","specialty":"","countries":[]},
    countTotal: 2,
    countFiltered: 2,
    datatable: {"cols":[{"label":"","type":"string"},{"label":"","type":"number"},{"type":"string","p":{"role":"annotation"}},{"type":"string","p":{"role":"style"}}],"rows":[{"c":[{"v":"Mean"},{"v":1.8},{"v":1.8},{"v":""}]},{"c":[{"v":"BB-456"},{"v":2.23},{"v":2.2},{"v":""}]},{"c":[{"v":"AA-123"},{"v":2.23},{"v":2.2},{"v":""}]},{"c":[{"v":"FF-789"},{"v":1.85},{"v":1.9},{"v":""}]},{"c":[{"v":"EE-456"},{"v":1.75},{"v":1.8},{"v":""}]},{"c":[{"v":"DD-123"},{"v":1.5},{"v":1.5},{"v":""}]},{"c":[{"v":"CC-789"},{"v":1.25},{"v":1.3},{"v":""}]}]}
  },
  {
    chartmachinename:"DoctorsPromote",
    charttype: "DoctorsPromote_Chart",
    drilldown: {"countries":["USA"],"specialties":["Oncology","Cardiology"],"regions":[]},
    filter: {"country":"","region":"","specialty":"","countries":[]},
    countTotal: 2,
    countFiltered: 2,
    datatable: {"ds":{"label":"Dissatisfied","append":"(0 brands promoted)","count":0,"perc":0,"show":"0%"},"sa":{"label":"Satisfied","append":"(>0 brands promoted)","count":4,"perc":100,"show":"100%"},"se":{"label":"Satisfied (Exclusive)","append":"(1 brand promoted)","count":0,"perc":0,"show":"0%"},"ss":{"label":"Satisfied (Shared)","append":"(>1 brands promoted)","count":4,"perc":100,"show":"100%"}}
  },
  {
    chartmachinename:"PromotersPromote",
    charttype: "PromotersPromote_Chart",
    drilldown: {"countries":["USA"],"specialties":["Oncology","Cardiology"],"regions":[]},
    filter: {"country":"","region":"","specialty":"","countries":[]},
    countTotal: 2,
    countFiltered: 2,
    datatable: {
      "cols":[
        {"label":"Brand","type":"string"},
        {"label":"Average number of other brands promoted","type":"number"},
        {"label":"Most commonly promoted competitor","type":"string"},
        {"label":"%","type":"string"}
      ],
      "rows":[
        {"c":[{"v":"AA-123"},{"v":4.5},{"v":"BB-456"},{"v":"50%"}]},
        {"c":[{"v":"BB-456"},{"v":4.8},{"v":"AA-123"},{"v":"75%"}]},
        {"c":[{"v":"CC-789"},{"v":4.8},{"v":"AA-123"},{"v":"75%"}]},
        {"c":[{"v":"DD-123"},{"v":4.5},{"v":"AA-123"},{"v":"75%"}]},
        {"c":[{"v":"EE-456"},{"v":4.5},{"v":"AA-123"},{"v":"75%"}]},
        {"c":[{"v":"FF-789"},{"v":4.5},{"v":"AA-123"},{"v":"75%"}]}]}
  },
  {
    chartmachinename:"DetractorsPromote",
    charttype: "DetractorsPromote_Chart",
    drilldown: {"countries":["USA"],"specialties":["Oncology","Cardiology"],"regions":[]},
    filter: {"country":"","region":"","specialty":"","countries":[]},
    countTotal: 2,
    countFiltered: 2,
    datatable: [
      {"title":"AA-123 Detractors promote these brands...","cols":[{"label":"","type":"string"},{"label":"% of AA-123 detractor","type":"string"}],"rows":[{"c":[{"v":"FF-789"},{"v":"0%"}]},{"c":[{"v":"EE-456"},{"v":"0%"}]},{"c":[{"v":"DD-123"},{"v":"0%"}]},{"c":[{"v":"CC-789"},{"v":"0%"}]},{"c":[{"v":"BB-456"},{"v":"0%"}]}]},
      {"title":"BB-456 Detractors promote these brands...","cols":[{"label":"","type":"string"},{"label":"% of BB-456 detractor","type":"string"}],"rows":[{"c":[{"v":"FF-789"},{"v":"0%"}]},{"c":[{"v":"EE-456"},{"v":"0%"}]},{"c":[{"v":"DD-123"},{"v":"0%"}]},{"c":[{"v":"CC-789"},{"v":"0%"}]},{"c":[{"v":"AA-123"},{"v":"0%"}]}]},
      {"title":"CC-789 Detractors promote these brands...","cols":[{"label":"","type":"string"},{"label":"% of CC-789 detractor","type":"string"}],"rows":[{"c":[{"v":"FF-789"},{"v":"0%"}]},{"c":[{"v":"EE-456"},{"v":"0%"}]},{"c":[{"v":"DD-123"},{"v":"0%"}]},{"c":[{"v":"BB-456"},{"v":"50%"}]},{"c":[{"v":"AA-123"},{"v":"50%"}]}]},
      {"title":"DD-123 Detractors promote these brands...","cols":[{"label":"","type":"string"},{"label":"% of DD-123 detractor","type":"string"}],"rows":[{"c":[{"v":"FF-789"},{"v":"0%"}]},{"c":[{"v":"EE-456"},{"v":"0%"}]},{"c":[{"v":"CC-789"},{"v":"0%"}]},{"c":[{"v":"BB-456"},{"v":"0%"}]},{"c":[{"v":"AA-123"},{"v":"0%"}]}]},
      {"title":"EE-456 Detractors promote these brands...","cols":[{"label":"","type":"string"},{"label":"% of EE-456 detractor","type":"string"}],"rows":[{"c":[{"v":"FF-789"},{"v":"0%"}]},{"c":[{"v":"DD-123"},{"v":"0%"}]},{"c":[{"v":"CC-789"},{"v":"0%"}]},{"c":[{"v":"BB-456"},{"v":"0%"}]},{"c":[{"v":"AA-123"},{"v":"0%"}]}]},
      {"title":"FF-789 Detractors promote these brands...","cols":[{"label":"","type":"string"},{"label":"% of FF-789 detractor","type":"string"}],"rows":[{"c":[{"v":"EE-456"},{"v":"0%"}]},{"c":[{"v":"DD-123"},{"v":"0%"}]},{"c":[{"v":"CC-789"},{"v":"0%"}]},{"c":[{"v":"BB-456"},{"v":"50%"}]},{"c":[{"v":"AA-123"},{"v":"50%"}]}]}
    ]
  },
  {
    chartmachinename:"PromVsDetrPromote",
    charttype: "PromVsDetrPromote_Chart",
    drilldown: {"countries":["USA"],"specialties":["Oncology","Cardiology"],"regions":[]},
    filter: {"country":"","region":"","specialty":"","countries":[]},
    countTotal: 2,
    countFiltered: 2,
    datatable: [{"title":"EE-456: How much more of my brand do Promoters prescribe versus Detractors?","cols":[{"label":"% of market share in each segment","type":"string"},{"label":"","type":"number"},{"type":"string","p":{"role":"annotation"}},{"type":"string","p":{"role":"style"}}],"rows":[{"c":[{"v":"Detractors"},{"v":2},{"v":"0.0%"},{"v":""}]},{"c":[{"v":"Promoters"},{"v":1},{"v":"100.0%"},{"v":""}]}]},{"title":"AA-123: How much more of my brand do Promoters prescribe versus Detractors?","cols":[{"label":"% of market share in each segment","type":"string"},{"label":"","type":"number"},{"type":"string","p":{"role":"annotation"}},{"type":"string","p":{"role":"style"}}],"rows":[{"c":[{"v":"Detractors"},{"v":1},{"v":"0.0%"},{"v":""}]},{"c":[{"v":"Promoters"},{"v":2},{"v":"90.0%"},{"v":""}]}]},{"title":"DD-123: How much more of my brand do Promoters prescribe versus Detractors?","cols":[{"label":"% of market share in each segment","type":"string"},{"label":"","type":"number"},{"type":"string","p":{"role":"annotation"}},{"type":"string","p":{"role":"style"}}],"rows":[{"c":[{"v":"Detractors"},{"v":2},{"v":"0.0%"},{"v":""}]},{"c":[{"v":"Promoters"},{"v":1},{"v":"0.0%"},{"v":""}]}]},{"title":"CC-789: How much more of my brand do Promoters prescribe versus Detractors?","cols":[{"label":"% of market share in each segment","type":"string"},{"label":"","type":"number"},{"type":"string","p":{"role":"annotation"}},{"type":"string","p":{"role":"style"}}],"rows":[{"c":[{"v":"Detractors"},{"v":1},{"v":"0.0%"},{"v":""}]},{"c":[{"v":"Promoters"},{"v":1},{"v":"0.0%"},{"v":""}]}]},{"title":"BB-456: How much more of my brand do Promoters prescribe versus Detractors?","cols":[{"label":"% of market share in each segment","type":"string"},{"label":"","type":"number"},{"type":"string","p":{"role":"annotation"}},{"type":"string","p":{"role":"style"}}],"rows":[{"c":[{"v":"Detractors"},{"v":2},{"v":"0.0%"},{"v":""}]},{"c":[{"v":"Promoters"},{"v":0},{"v":"0.0%"},{"v":""}]}]},{"title":"FF-789: How much more of my brand do Promoters prescribe versus Detractors?","cols":[{"label":"% of market share in each segment","type":"string"},{"label":"","type":"number"},{"type":"string","p":{"role":"annotation"}},{"type":"string","p":{"role":"style"}}],"rows":[{"c":[{"v":"Detractors"},{"v":3},{"v":"1.7%"},{"v":""}]},{"c":[{"v":"Promoters"},{"v":0},{"v":"0.0%"},{"v":""}]}]}]
  },
  {
    chartmachinename:"PPDBrandMessages",
    charttype: "PPDBrandMessages_Chart",
    drilldown: {"countries":["USA"],"specialties":["Oncology","Cardiology"],"regions":[]},
    filter: {"country":"","region":"","specialty":"","countries":[]},
    countTotal: 2,
    countFiltered: 2,
    datatable: {"cols":[{"label":"Brand association question","type":"string"},{"label":"Detractor","type":"number"},{"label":"Passive","type":"number"},{"label":"Promoter","type":"number"},{"label":"Lowest confidence level","type":"number","p":{"role":"interval"}},{"label":"Highest confidence level","type":"number","p":{"role":"interval"}},{"label":"Detractor","type":"number"},{"label":"Passive","type":"number"},{"label":"Promoter","type":"number"},{"label":"Lowest confidence level","type":"number","p":{"role":"interval"}},{"label":"Highest confidence level","type":"number","p":{"role":"interval"}},{"label":"Detractor","type":"number"},{"label":"Passive","type":"number"},{"label":"Promoter","type":"number"},{"label":"Lowest confidence level","type":"number","p":{"role":"interval"}},{"label":"Highest confidence level","type":"number","p":{"role":"interval"}},{"label":"Detractor","type":"number"},{"label":"Passive","type":"number"},{"label":"Promoter","type":"number"},{"label":"Lowest confidence level","type":"number","p":{"role":"interval"}},{"label":"Highest confidence level","type":"number","p":{"role":"interval"}},{"label":"Detractor","type":"number"},{"label":"Passive","type":"number"},{"label":"Promoter","type":"number"},{"label":"Lowest confidence level","type":"number","p":{"role":"interval"}},{"label":"Highest confidence level","type":"number","p":{"role":"interval"}},{"label":"Detractor","type":"number"},{"label":"Passive","type":"number"},{"label":"Promoter","type":"number"},{"label":"Lowest confidence level","type":"number","p":{"role":"interval"}},{"label":"Highest confidence level","type":"number","p":{"role":"interval"}}],"rows":[{"c":[{"v":"it just works"},{"v":50},{"v":28.6},{"v":40},{"v":15.288},{"v":16.072},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null}]},{"c":[{"v":"just painful"},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":41.7},{"v":85.7},{"v":60},{"v":23.128},{"v":23.912},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null}]},{"c":[{"v":"kind of cool"},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":33.3},{"v":100},{"v":80},{"v":30.968},{"v":31.752}]},{"c":[{"v":"mildly pointless"},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":66.7},{"v":28.6},{"v":60},{"v":23.128},{"v":23.912},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null}]},{"c":[{"v":"painfull side effects"},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":58.3},{"v":71.4},{"v":60},{"v":23.128},{"v":23.912},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null}]},{"c":[{"v":"risk of death"},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":41.7},{"v":71.4},{"v":40},{"v":15.288},{"v":16.072},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null},{"v":null}]}]}
  },
  {
    chartmachinename:"DNA",
    charttype: "DNA_Chart",
    drilldown: {"countries":["USA"],"specialties":["Oncology","Cardiology"],"regions":[]},
    filter: {"country":"","region":"","specialty":"","countries":[]},
    countTotal: 2,
    countFiltered: 2,
    datatable: [
      {
        brand: 'What\x20is\x20AA\x2D123\x27s\x20brand\x20DNA\x3F',
        detractors: [
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Edfgdf\x20fg\x20sg\x20sdfg\x20sdfg\x20sdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Esdgr\x20fsddfg\x20fg\x20sdfg\x20sdfg\x20sdfg\x20sdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                    ],
        promoters: [
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Edfgs\x20sd\x20fsdf\x20gsdfg\x20sdfg\x20sdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Ef\x20sdfg\x20sfgsdf\x20gsdfgsdfsd\x20fg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                    ]
      },
            {
        brand: 'What\x20is\x20BB\x2D456\x27s\x20brand\x20DNA\x3F',
        detractors: [
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Es\x20dfg\x20sdfg\x20sdfg\x20sdfg\x20sdfg\x20sdfg\x20sdfg\x20sdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                    ],
        promoters: [
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Esdfg\x20sdfg\x20sdf\x20gsdf\x20gsdf\x20gsdf\x20g\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Edsfg\x20df\x20dfg\x20sdfg\x20sdfg\x20fg\x20fg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                    ]
      },
            {
        brand: 'What\x20is\x20CC\x2D789\x27s\x20brand\x20DNA\x3F',
        detractors: [
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Esd\x20fgsd\x20fgsdfg\x20sdfg\x20sdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Esdfg\x20sdfg\x20sdfg\x20sdfg\x20sdfg\x20sdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                    ],
        promoters: [
                    ]
      },
            {
        brand: 'What\x20is\x20DD\x2D123\x27s\x20brand\x20DNA\x3F',
        detractors: [
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Es\x20dfgsd\x20fgsdf\x20gsdfg\x20sdfgsdfg\x20sdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Esdfgsdfg\x20sdfg\x20sdfg\x20sdfg\x20sdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                    ],
        promoters: [
                    ]
      },
            {
        brand: 'What\x20is\x20EE\x2D456\x27s\x20brand\x20DNA\x3F',
        detractors: [
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Esdfgsdfgsdfgsdf\x20gsdf\x20gsdfg\x20sdfg\x20sdfg\x20sdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                    ],
        promoters: [
                    ]
      },
            {
        brand: 'What\x20is\x20FF\x2D789\x27s\x20brand\x20DNA\x3F',
        detractors: [
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Eddfdfgdfgdfg\x20\x20sdfgsd\x20fgsdf\x20gsdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Esdfgsdfg\x20sdfg\x20sdfg\x20sdfg\x20sdfg\x20sdfgsdfg\x20sd\x20fgsdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                    ],
        promoters: [
                        "\x3Cspan\x3E\x22\x3C\x2Fspan\x3Esdfg\x20sdfg\x20dfgdfgdfgdfg\x20dfgsdfg\x20sdfg\x20sdfgsdfg\x3Cspan\x3E\x22\x3C\x2Fspan\x3E",
                    ]
      },
    ]
  }
];