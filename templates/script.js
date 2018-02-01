var map;
var parts = "VoteDistrict";
var dyed = false;
var plygns = [];
var plygndata = [];
var sortableby = [];
$("#loader").hide();

$(document).ready(function(){
  $('select').select();
  $('select').on('contentChanged', function() {
    // re-initialize (update)
    $(this).select();
  });
  $('select#partselect').change(function(){
    getDistricts(this.value);
    parts = this.value;
  });
  $('select#dyeselect').change(function(){
    dyeDistricts(this.value);
    console.log(this.value);
  });
  $('select').change();


});

$(window).scroll(function(e){
  //if ($('div#container').outerHeight() > $(window).height()) {
    $('#mapWrapperWrapper').css({'margin-top': $(window).scrollTop()+'px'});
  //}
});


function initMap() {
  var latlng = new google.maps.LatLng(47.719363, 9.1499953);

  var options = {
    zoom: 12,
    center: latlng,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    disableDefaultUI: true
  };

  map = new google.maps.Map(document.getElementById('map'), options);
}

function getDistricts(type) {
  api({access: "api",request: "getVisualization",type: type},function(data){
    plygndata = [];
    // plygndata[].coords = data.payload.data;
    sortableby = data.payload.sortableBy;
    $('#dyeselectcontainer').empty();
    html = "<option value='false'>Zufällig</option>";
    sortableby.forEach(function(s){
      html = html+'<option value="'+s+'">'+s+'</option>';
    });
    $('#dyeselectcontainer').html("<select>"+html+"</select><label>Sortieren nach</label>");
    $('#dyeselectcontainer select').select();
    $('#dyeselectcontainer select').change(function(){
      if (this.value != 'false') {
        dyeDistricts(this.value);
      } else {
        dyed = false;
        getDistricts(parts);
      }
    });
    M.updateTextFields();
    var colors = [];
    data.payload.data.forEach(function(ring){
      //console.log(ring);
      colors[ring.data.id] = colors[ring.data.id] || getRandomColor();
      plygndata.push({
        paths: ring.data.polygon,
        did:ring.data.id,
        strokeColor: colors[ring.data.id],
        strokeOpacity: 0.7,
        strokeWeight: 3,
        fillColor: colors[ring.data.id], //
        fillOpacity: 0.7
      });
    });

    drawDistricts(plygndata);
    if (dyed) {
      dyeDistricts($('#dyeselectcontainer select').value());
    }
  });
}

function dyeDistricts(crit){
  console.log(crit);
  api({access:"api",request:"getRanking",type:parts, criteria: crit}, function(data){
    console.log(data);
    plygndata.forEach(function(pg,key){
      //color = "rgba("+parseInt(parseFloat(data.payload.ranking[parseInt(pg.did)])*255)+",0,0,255)";
      //color = "#"+parseInt(parseFloat(data.payload.ranking[parseInt(pg.did)])*255).toString(16)+"0000";
      color = getColorForPercentage(data.payload.ranking[parseInt(pg.did)]);
      plygndata[key].strokeColor = color;
      plygndata[key].fillColor = color;
    });
    min = precisionRound(data.payload.range.min, 0);
    max = precisionRound(data.payload.range.max, 0)
    $('p#colorRange').text(data.payload.range.min+" - "+data.payload.range.max);
    console.log(plygndata);
    drawDistricts(plygndata);
    $('select').select();
    dyed = true;
  });
}

function drawDistricts(data, keep=false) {
  if (!keep) {
    deletePolygons();
  }
  var i = 0;
  if (Array.isArray(data)) {
    data.forEach(function(plygn){

      //console.log(plygn);
      plygns[i] = new google.maps.Polygon(plygn);
      plygns[i].setMap(map);
      plygns[i].addListener('click', function(){polygonClicker(plygn.did)});
      i++;
    });
  }
}

function drawInfo(id) {
  $("#selected").text("zu "+parts+" "+id);
  api({access: "api",request: "getData", type: parts, id: id},function(data){
    var colors = [];
    //console.log(data);
    var i = 0;
    $('#info .tmp').remove();
    var draweddata = [];
    if (data.payload) {
      Object.keys(data.payload.data).forEach(function(key){
        if (typeof data.payload.data[key] != 'object' && !Array.isArray(data.payload.data[key])) {
          draweddata[key] = data.payload.data[key];
        } else if (key == "inhabitants") {
          //console.log(data.payload.data[key]);
          writeDataToInhabitantGraph(data.payload.data[key]);
        }
      });
      if (draweddata["name"]) {
        $("#selected").text("zu "+draweddata["name"]);
      }
    }


    drawTable(draweddata);
  });
}

function writeDataToInhabitantGraph(data) {
  $("#charts #inhabitants").empty();
  var datasets = [];
  var labels = [];
  labels.push("female");
  labels.push("male");
  datasets.push({data:[
    data[2016].inhabitantsFemale , data[2016].inhabitantsTotal - data[2016].inhabitantsFemale
  ], backgroundColor: [
    "#e28cdb",  "#8cbee2"
  ]});
  new Chart($("#charts #inhabitants"), {
    type: 'pie',
    data: {
      datasets: datasets,
      labels: labels
    },
  });

  var datasets = [];
  var labels = [];
  labels.push("0-18");
  labels.push("18-30");
  labels.push("30-40");
  labels.push("40-50");
  labels.push("50-60");
  labels.push("60+");
  datasets.push({label: "Einwohner", data:[
    data[2016].inhabitantsUnder18,
    data[2016].inhabitants18to30,
    data[2016].inhabitants30to40,
    data[2016].inhabitants40to50,
    data[2016].inhabitants50to60,
    data[2016].inhabitantsOver60,
  ], backgroundColor: [
    getColorForPercentage(1/6),
    getColorForPercentage(2/6),
    getColorForPercentage(3/6),
    getColorForPercentage(4/6),
    getColorForPercentage(5/6),
    getColorForPercentage(6/6)
  ]});
  new Chart($("#charts #inhabitantsbyAge"), {
    type: 'bar',
    data: {
      datasets: datasets,
      labels: labels
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
  });
}

function polygonClicker(id) {
  drawInfo(id);

  $('#graphfiltertype').val(parts);
  $('#graphfilterid').val(id);
  M.updateTextFields();

}

function deletePolygons() {
  plygns.forEach(function(pg){
    pg.setMap(null);
  });
  plygns = [];
}

function getRandomColor() {
  var letters = '0123456789ABCDEF';
  var color = '#';
  for (var i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}

function drawTable(data){
  $("table#info").empty();
  for (var index in data) {
    if (isNumber(data[index])) {
      val = precisionRound(parseFloat(data[index]),0);
    } else {
      val = data[index];
    }
    $("table#info").append('<tr><td>'+index+':</td><td>'+val+'</td></tr>');
  }

}

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

function precisionRound(number, precision) {
  var factor = Math.pow(10, precision);
  return Math.round(number * factor) / factor;
}

var percentColors = [
    { pct: 0.0, color: { r: 0x70, g: 0x98, b: 0xd8 } },
    { pct: 0.5, color: { r: 0xb1, g: 0x5c, b: 0xcb } },
    { pct: 1.0, color: { r: 0xba, g: 0x09, b: 0x09 } } ];

var getColorForPercentage = function(pct) {
    for (var i = 1; i < percentColors.length - 1; i++) {
        if (pct < percentColors[i].pct) {
            break;
        }
    }
    var lower = percentColors[i - 1];
    var upper = percentColors[i];
    var range = upper.pct - lower.pct;
    var rangePct = (pct - lower.pct) / range;
    var pctLower = 1 - rangePct;
    var pctUpper = rangePct;
    var color = {
        r: Math.floor(lower.color.r * pctLower + upper.color.r * pctUpper),
        g: Math.floor(lower.color.g * pctLower + upper.color.g * pctUpper),
        b: Math.floor(lower.color.b * pctLower + upper.color.b * pctUpper)
    };
    return 'rgb(' + [color.r, color.g, color.b].join(',') + ')';
    //return '#'+parseInt(color.r).toString(16)+parseInt(color.g).toString(16)+parseInt(color.b).toString(16);
    // or output as hex if preferred
}


function api(data, successfn){
  try {
    $("#loader").show();
    $.ajax({
      url: "",
      data: data,
      crossDomain: true,
      dataType: "json",
      tryCount: 0,
      retryLimit: 5,
      error : function(xhr, textStatus, errorThrown ) {
        if (textStatus == 'timeout') {
            this.tryCount++;
            if (this.tryCount <= this.retryLimit) {
                //try again
                console.log("retry");
                $.ajax(this);
                return;
            }
            return;
        }
        $("#loader").hide();
        if (xhr.status == 500) {
            M.toast("Error 500: Internal Server Error");
            console.log("500");
        } else {
          console.log(xhr.responseText);
          M.toast({html: xhr.status+": "+xhr.statusText});
        }
      },
      success: function(data) {

        $("#loader").hide();
        if (data.code == 200) {
          successfn(data);
        } else {
          console.log(data);
          M.toast({html: "Scripted Error "+data.code+": "+data.message})
        }
      }
    });
  } catch (e) {
    $("#loader").hide();
    console.log("error: "+e);
    M.toast({html: "error: "+e});
  }

}
