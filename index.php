<!DOCTYPE html>
<html>
	<head>
		<style type="text/css">
			
		</style>
	</head>
	<body>
		<script type="text/javascript" src="jquery.min.js"></script>
		<script src="chart.min.js"></script>
		<script type="text/javascript" src="jsonFileLister.php"></script>
		<script src = "https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.9.1/underscore-min.js">
        </script>

		<div>
			<select id="stateChoice"></select>
			<select id="timeFrameChoice"></select>
			<button id="submit">Submit</button>
		</div>

		<!--Add a link back to the home page-->
		<p align="right" style="vertical-align: top;">
		  <a href="https://crayleblanc.github.io/lamda_main_page/">Home Page</a>
		</p>
		
		<div id="chart-container">
			<canvas id="demoCanvas"></canvas>
		</div>
		<br>
		<br>
		<br>

		<div id="line-chart-container">
			<canvas id="lineCanvas"></canvas>
		</div>

		<script type="text/javascript">
			var submitButton=document.getElementById("submit");
			var stateOption=document.getElementById("stateChoice");
			var timeFrameOption=document.getElementById("timeFrameChoice");

			//list out all of the json files
			//for each of the json files, append to the json list
			//when ready, pop the first item in the json list, and add to the chart for that given state
			//continue for each of the items in the list

			//the jsonDataDict will be a nested dictionary containing all the json files. The key will be the index of the json file. 0 is the oldest
			//timeframe and the greatest index will be the most recent timeframe
			var jsonDataDict={};

			//timeFrameNameDict is a dictionary of the date values of all the timeframes
			var timeFrameNameDict={};

			//this is a list of all the indexes
			var indexList=[];

			var raceDemographics = (function () {
			    var json = null;
			    $.ajax({
			        'async': false,
			        'global': false,
			        'url': 'race_demo_by_state.json',
			        'dataType': "json",
			        'success': function (data) {
			            json = data;
			        }
			    });
			    return json;
			})();

			//for each of the names in the json_file array, open that file and append the data contained within to the jsonList array
			jsonFiles.forEach(function(json_file){
				var json_data=null;
				$.ajax({
			        'async': false,
			        'global': false,
			        'url': 'json_vaccine_files\\' + json_file + '.json',
			        'dataType': "json",
			        'success': function (data) {
			            json_data = data;
			        }
			});

			    var splitFileName=json_file.split("_");
			    var fileIndex=parseInt(splitFileName[0]);
			    var timeFrameDate=splitFileName[1] + " " + splitFileName[2] + ", " + splitFileName[3];

			    jsonDataDict[fileIndex]=json_data;
			    timeFrameNameDict[fileIndex]=timeFrameDate;

			    //add the timeframe choice to the dropdown menu of timeframe choices
			    var current_timeFrame = document.createElement("option");
			    current_timeFrame.textContent = timeFrameDate;
			    //when you search for the value, it'll be by the file's index
			    current_timeFrame.value = fileIndex;
			    timeFrameOption.appendChild(current_timeFrame);
			    indexList.push(fileIndex);
			});

			//this returns the list as numerically increasing, for some reason .sort() by itself will sort the numbers 
			//alphabetically by default
			indexList.sort((a,b)=>a-b);

			var stateKeys=[];

			for(key in raceDemographics){
				stateKeys.push(key);
				var currentState = document.createElement("option");
			    currentState.textContent = key;
			    currentState.value = key;
			    stateOption.appendChild(currentState);
			}

			submitButton.addEventListener('click', displayChart);

			var raceLabels=["White","Black","Hispanic","Asian"];
			var vaccineLabels=["White % of Vaccinations","Black % of Vaccinations","Hispanic % of Vaccinations","Asian % of Vaccinations"];

			async function queryData(stateSelection, timeFrameSelection){

				//this will select the dictionary of the timeframe vaccine data by its index
				var selectedVaccineTimeframe=jsonDataDict[timeFrameSelection];

				//this will select just the name of the timeframe, for displaying on the chart
				var timeFrameDate=timeFrameNameDict[timeFrameSelection];

				var raceData=[];

				

				vaccineLabels.forEach(function(race){
					raceData.push(selectedVaccineTimeframe[stateSelection][race]);
				});

				return raceData;

			}

			async function createChart(race_vax_data, stateSelection){
				var demoData={
					labels:raceLabels,
					datasets:[
						{
							label:`Percent fully vaccinated for ${stateSelection}`,
							data:race_vax_data,
							borderColor:'rgba(30,220,20,1)',
							backgroundColor:'rgba(30,220,20,1)'
						}
					]
				};

				var ctx= $("#demoCanvas");

				var barGraph=new Chart(ctx, {
					type:'bar',
					data: demoData,
					options:{
						plugins:{
							title:{
								display:true,
								text:'Racial Percent of State Population vs Percent of Vaccinations by Race Per State'
							}
						}
					}
				});

				var whiteVaxData=[];
				var blackVaxData=[];
				var hispanicVaxData=[];
				var asianVaxData=[];
				var dateLabels=[];

				indexList.forEach(function(currentIndex){
					//selects the timeframe with the currentIndex and the state with the stateSelection
					var currentDictionary=jsonDataDict[currentIndex][stateSelection];

					//pushes all of the line data onto their respective arrays
					whiteVaxData.push(currentDictionary["White % of Vaccinations"]);
					blackVaxData.push(currentDictionary["Black % of Vaccinations"]);
					hispanicVaxData.push(currentDictionary["Hispanic % of Vaccinations"]);
					asianVaxData.push(currentDictionary["Asian % of Vaccinations"]);

					//pushes the timeframe name onto the labels array, the oldest date is index 0 and the newset date is the largest index
					dateLabels.push(timeFrameNameDict[currentIndex]);
				});

				var lineData={
					labels:dateLabels,
					datasets:[
						{
						  label: 'White',
					      data: whiteVaxData,
					      borderColor: 'rgb(200,50,50)',
					      backgroundColor: 'rgb(200,50,50)',
						},
						{
						  label: 'Black',
					      data: blackVaxData,
					      borderColor: 'rgb(50,200,50)',
					      backgroundColor: 'rgb(50,200,50)',
						},
						{
						  label: 'Hispanic',
					      data: hispanicVaxData,
					      borderColor: 'rgb(135,30,200)',
					      backgroundColor: 'rgb(135,30,200)',
						},
						{
						  label: 'Asian',
					      data: asianVaxData,
					      borderColor: 'rgb(230,80,30)',
					      backgroundColor: 'rgb(230,80,30)',
						}
					]
				};

				var ctx2=$("#lineCanvas");

				var lineGraph=new Chart(ctx2,{
					type:'line',
					data:lineData,
					options:{
						plugins: {
						      title: {
						        display: true,
						        text: `Percent of Fully Vaccinated by Race Over Time for ${stateSelection}`
						      }
						}
					}
				});
			}

			async function displayChart(){
				//remove the canvas elements and then reappend them to their respective div elements
				//clears the old canvas to display the new values on the chart
				$('#demoCanvas').remove();
				$('#lineCanvas').remove();
				$('#chart-container').append('<canvas id="demoCanvas"><canvas>')
				$('#line-chart-container').append('<canvas id="lineCanvas"><canvas>');

				var stateSelection=document.getElementById('stateChoice').value;
				var timeFrameSelection=document.getElementById('timeFrameChoice').value;

				var race_vax_values=await queryData(stateSelection, timeFrameSelection);
				await createChart(race_vax_values, stateSelection);
			}
			
		</script>
	</body>
</html>
