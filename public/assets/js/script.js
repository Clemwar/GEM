var fileCount = $('.js-use').data('medialength');
var removeButton = "<button type='button' onclick='removeFile($(this));'></button>";

function removeFile(ob)
{
	ob.parent().parent().remove();
}

function createAddFile(fileCount)
{
	// grab the prototype template
	var newWidget = $("#filesProto").attr('data-prototype');
	// replace the "__name__" used in the id and name of the prototype
	newWidget = newWidget.replace(/__name__/g, fileCount);
    
    newWidget = "<div style='display:none'>" + newWidget + "</div>";
    
    hideStuff = "";
	hideStuff += "<div id='jsRemove" + fileCount + "' style='display: none;'>";
		hideStuff += removeButton;
	hideStuff += "</div>";

	hideStuff += "<div id='jsPreview" + fileCount + "'>";
	hideStuff += "</div>";

	hideStuff += "<div>";
		hideStuff += "<button type='button' id='jsBtnUpload" + fileCount +"'>";
			hideStuff += "Ajouter une image";
		hideStuff += "</button>";
	hideStuff += "</div>";

	$("#filesBox").append("<div>" + hideStuff + newWidget + "</div>");

	// On click => Simulate file behaviour
	$("#jsBtnUpload" + fileCount).on('click', function(e){
		$('#ateliers_medias__name__' + fileCount + '_file').trigger('click');
	});

	// Once the file is added
	$('#ateliers_medias__name__' + fileCount + '_file').on('change', function() {
		// Show its name
		fileName = $(this).prop('files')[0].name;
		$("#jsPreview" + fileCount).append(fileName);
		// Hide the add file button
		$("#jsBtnUpload" + fileCount).hide();
		// Show the remove file button
		$("#jsRemove" + fileCount).show();

		// Create another instance of add file button and company
		createAddFile(parseInt(fileCount)+1);
	});
}   

$(document).ready(function(){
        createAddFile(fileCount);
        fileCount++;
});