console = console || {
    log: function(){
       // makes sure console.log is defined to avoid errors in browsers that don't support it 
    }
};

/** MVDTouch singleton */
var MVDTouch = {
    /**
     * Indicates whether MVDTouch demo is getting data from local files or from MVD server
     * Acceptible values are 'local' and 'remote'
     * @type String
     */
    mode: 'remote',
    /**
     * The name of the MVD we are working with for the demo
     * @type String
     */
    mvdName: 'tess',
    localPath: 'data/',
    remotePath: '/joomla/index.php',
    syncScroll: false,
    BOTTOM: 0,
    TOP: 1,
    getVersionLabelCount: function(){
        var numEditions = this.getEditionCount();
        // all non-edition versions have two labels (one for each edition group)
        // + each edition version has one label : these appear at the top and bottom of the slider
        return this.data.versions.length * numEditions + numEditions - 1;
    },
    getEditionCount: function(){
        // Assume a single unnamed edition if no edition titles were specified in config
        return Math.max(1, this.data.editions.length);
    },
    /** return the version corresponding the the value from the slider */
    getVersion: function(index){
        var totalNum = this.getVersionLabelCount(), 
            numVersions = this.data.versions.length,
            // slider min value is at bottom, but we want value relative from top, so adjust by subtraction
            adjustedIndex = (totalNum - index);
        // slider values are: topEditionVersion, (all other versions, listed once for each version), bottomEditionVersion
        var version;
        if (adjustedIndex == 0){
            version = this.data.topEditionVersion;
        } else if (adjustedIndex == totalNum) {
            version = this.data.bottomEditionVersion;
        } else {
            version = this.data.versions[(adjustedIndex - 1) % numVersions];
        }
        return version;
    },
    positionSliderLabels: function(){
        var labelOffset = 5, // adjustment so that labels line up with handles
            sliderHeight = $('#slider').height(),
            numLabels = this.getVersionLabelCount()
            labelSpacing = sliderHeight / numLabels,
            numEditions = this.getEditionCount();
        if (labelSpacing > 0){
            $('.version-label').css('top',
                function(index){
                    var labelPosition = index;
                    if (numEditions > 0) {
                        labelPosition++;
                    }
                    return (Math.floor(labelSpacing * labelPosition) - labelOffset) + 'px';
                }
            );
            $('.slider-label-sep').css('top',
                function(index){
                    return (sliderHeight / MVDTouch.getEditionCount() * (index + 1)- labelOffset) + 'px';
                }
            );
            $('.edition-label').css('top',
                function(index){
                    return (index === 0 ? 
                        -6 : (Math.floor(labelSpacing * numLabels ) - labelOffset - 6))+ 'px';
                    
                }
            );
        }
    },
    onSliderChange: function(event, ui) {
        function generateVersionPath(thisVersion, otherVersion, isTop) {
            var path;
            if (MVDTouch.mode === 'local'){
                path = MVDTouch.localPath + MVDTouch.mvdName;
                if (thisVersion.type === 'MVD'){
                    path += (isTop ? '/lhs/' : '/rhs/') + thisVersion.id + 'vs';
                    if (otherVersion.type === 'MVD' && thisVersion.id !== otherVersion.id){
                        path += otherVersion.id + '.html';
                    } else {
                       //other version is same or image, we won't be comparing
                        path += "/lhs/" + thisVersion.id + 'vs' + thisVersion.id + '.html';
                    }
                } else {
                    // non-MVD resources e.g. images
                    path += "/" + thisVersion.type + "/" + thisVersion.id + ".html";
                }
            } else {
                path = MVDTouch.remotePath +
                    '?option=com_mvd&view=fragment&format=raw&name=' +
                    '&name=' + MVDTouch.mvdName +
                    '&version1=' + thisVersion.id + 
                    '&version2=' + otherVersion.id +
                    '&side=' + (isTop ? 'lhs' : 'rhs');
                    //console.log("remote path is " + path);
            }
            return path;
        }
        var numVersions = this.data.versions.length,
            totalNum = this.getVersionLabelCount(),
            bottomVal = ui.values[this.BOTTOM],
            topVal = ui.values[this.TOP];
        if (topVal < bottomVal){
            var tmp = topVal;
            topVal = bottomVal;
            bottomVal = tmp;
        }
        // If the user has moved both slider handles to the same value, show one pane
        // Checking for originalEvent ensures we only move handles after a mouse event
        if (topVal === bottomVal && event.originalEvent){
            // hide one view
            $('div.ui-layout-center').layout().hide('south');
        } else {
            // show both views
            $('div.ui-layout-center').layout().show('south');
        }
        
        // look up version ids from config to construct paths to texts
        var topVersion = this.getVersion(topVal),
            bottomVersion = this.getVersion(bottomVal),
            topPath = generateVersionPath(topVersion, bottomVersion, true),
            bottomPath = generateVersionPath(bottomVersion, topVersion, false);

        // load texts, the final arg indicates whether top or bottom view was changed
        this.loadVersions(topPath, bottomPath, $(ui.handle).index());
        // TODO: load explanatory notes for selected edition(s)
        var topEdition = this.data.editions[Math.floor(topVal / numVersions)];
        var bottomEdition = this.data.editions[Math.floor(bottomVal / numVersions)];
       
        /*console.log("top is from " + 
            Math.floor(topVal / numVersions),
            topEdition, "bottom is from " + 
            Math.floor(bottomVal / numVersions),bottomEdition );
        */
    },
    synchronizeTextScroll: function(viewThatChanged, viewToScroll) {
        //console.log("synchronize text scroll " + this.syncScroll);
        // check whether views should be synchronized (images not synchronized at present)
        if (this.syncScroll){
            // find the nearest span that has an id to the center of the scrolledView
            // FIXME: find out how to scale/adjust this value
            var syncPoint = viewThatChanged.scrollTop() / 15;

            // Find the first span with an id that is visible within view that changed
            var syncSpan = 
                viewThatChanged.find('span[id]').filter(function(){
                    var elTop = $(this).position().top;
                    return elTop >= (syncPoint - 30) && elTop <= syncPoint; 
                }).filter(':first');    
            //console.log(" sync " + syncPoint + "height" + viewThatChanged.height() + " element pos " + syncSpan.position().top + " " + syncSpan.offset().top);
            var syncSpanId = syncSpan.attr('id');
            //console.log(syncSpan.text());
            if (syncSpanId){
              var toScrollSpanId = (syncSpanId.charAt(0) === 'a' ? 'd' + syncSpanId.substring(1) : 'a' + syncSpanId.substring(1));
              var toScroll = viewToScroll.find('#' + toScrollSpanId);
              //console.log(document.getElementById(toScrollSpanId));
              //console.log(syncPoint + " " + syncSpanId + " " + toScrollSpanId, toScroll);
              
              if (toScroll.length >= 1 && toScroll.offset()){
                 this.syncScroll = false;
                 viewToScroll.animate(
                        {
                            scrollTop:toScroll.offset().top - viewToScroll.height() /2
                        },
                        {
                            duration:'fast',
                            complete: function(){
                                MVDTouch.syncScroll = true;
                            }
                        }
                 );
              }
              
            }
            
        }
    },
    /** Show markers for notes from TEI */
    showNoteMarkers: function(view) {
        var previousPosition;
        var defaultIndent = 3;
        var indent = defaultIndent;
        $(view).find('.note').each(function(i){
            var note = $(this);
            var newPosition = note.position();
            // create note marker in margin
            var noteMarker = $(document.createElement('div'))
                .appendTo($(view))
                .addClass('noteMarker')
                .html("[" + (i + 1) + "]")
                .css('top', newPosition.top + "px")
                .data('note', note)
                .toggle(
                    // toggle highlighting of word before note and note visibility on click
                    function(){
                        $(this).data('note')
                        .css('display','block')
                        .prevAll(':not(:empty):first')
                        .css('color','orange');
                    },
                    function(){
                        $(this).data('note')
                        .css('display','none')
                        .prevAll(':not(:empty):first')
                        .css('color','');
                    }
             );

             if (previousPosition === newPosition.top){
                indent = indent + noteMarker.width();
                noteMarker.css('left', indent + "px");
             } else {
                indent = defaultIndent;
             }
             previousPosition = newPosition.top;
             note.hide()
                .css('float','left')
                .css('width','auto')
                .css('height','auto')
                .css('position','absolute')
                .css('top', (newPosition.top + 20)+'px')
                .css('left', newPosition.left+'px');
             note.find('.note-content')
                .css('display','block')
                .css('max-width','500px')
                .css('padding','5px 8px 4px');
        });
        
    },
    loadVersions: function(topPath, bottomPath, updatedViewIndex) {
        var bottomView = $('#bottom-view').load(bottomPath, function(){
            MVDTouch.showNoteMarkers(bottomView);
        });
        var topView = $('#top-view').load(topPath, function(){
            MVDTouch.showNoteMarkers(topView);
        });
        if (this.syncScroll){
            // synchronize scroll positions
            if (updatedViewIndex === this.BOTTOM) {
                this.synchronizeTextScroll(bottomView,topView);
            } else {
                this.synchronizeTextScroll(topView,bottomView);
            }
        }
        
    },
    /** Create jQuery UI slider and version labels and load initial versions */
    init: function(){
        var labelContainer = $('#version-labels'),
            numEditions = this.data.editions.length,
            numLabels = this.getVersionLabelCount(),
            // only count non-edition versions
            numNonEditionLabels = this.data.versions.length * numEditions - 1;
        if (numNonEditionLabels <= 0){
            $('#top-view').html("No versions found");
            return;
        }
        $('#slider').slider({
            orientation: 'vertical',
            max: numLabels,
            values: [0, numLabels],
            step: 1,
            animate: true,
            change: $.proxy(this.onSliderChange, this)
        });
        // create labels for edition versions
        var topEdition = this.data.topEditionVersion,
            bottomEdition = this.data.bottomEditionVersion;
        if (topEdition){
            $(document.createElement('div'))
                    .addClass('edition-label')
                    .text(topEdition.title)
                    .appendTo(labelContainer);
        }
        if (bottomEdition){
            $(document.createElement('div'))
                    .addClass('edition-label')
                    .text(bottomEdition.title)
                    .appendTo(labelContainer);
        }
        // create labels for all non-edition versions
        for (var i = 0; i <= numNonEditionLabels; i++){
            var version = this.data.versions[i % this.data.versions.length];
            $(document.createElement('div'))
                .addClass('version-label')
                .text(version.title)
                .appendTo(labelContainer);
        }

        // Create edition  separators if this is a multi-edition demo
        // Separators are added between any number of editions, 
        // but editon labels only shown for first and last from config
        if (numEditions > 1){
            for (var j = 1; j < numEditions; j++){
                $(document.createElement('hr'))
                    .addClass('slider-label-sep')
                    .appendTo(labelContainer);
            }
        }
        // layout for slider pane - this needs to happen after slider 
        // and edition labels have been created so that height is correct
        $('div.ui-layout-east').layout({
            applyDefaultstyles: true,
            center: {
                paneSelector: '#slider-pane',
                onresize: $.proxy(this.positionSliderLabels, this)
            },
            east: {
                paneSelector: '#version-labels',
                size: 95,
                closable: false,
                resizable: false
            
            },
            // used to hold labels, now used for spacing
            south : {
                paneSelector: '#bottom-spacing',
                resizable: false,
                size: 5,
                closable: false
            },
            north: {
                paneSelector: '#top-spacing',
                resizable: false,
                size: 5,
                closable: false
            }
        });
        // Load initial texts
        this.onSliderChange(null,{values:[0, numLabels]});
        this.positionSliderLabels();
    },
    data: {
        // shortnames correspond to shortnames of editions from MVD
        editions: ['Clarendon', 'Penguin'],
        // add version data here if configuring local demo
        topEditionVersion: {},
        bottomEditionVersion: {},
        versions: [
            // all other versions
            /*
                 {
                        title: 'Manuscript',
                        shortName: 'MS',
                        type: 'MVD',
                        id: 1
                 }
             */
        ]       
    }
}

$(document).ready(function(){
    // Configure page layout
    $('body').layout({
        east: {
            spacing_open: 2,
            size: 120,
            closable: false,
            resizable: false
        },
        north: {
            spacing_open: 0,
            size: 20,
            closable: false,
            resizable: false
        }
    });
    $('div.ui-layout-center').layout({
        center: {
            applyDefaultStyles: true,
            paneSelector: '#top-view'
        },
        south: {
            applyDefaultStyles: true,
            paneSelector: '#bottom-view',
            closable: false,
            size: '.5' 
        }
    });
    // synchronize scrolling between text views
    /*$('#top-view').scroll(function(event){
        //console.log("top scroll",event)
        MVDTouch.synchronizeTextScroll($(this),$('#bottom-view'));
    });
    $('#bottom-view').scroll(function(event) {
            //console.log("bottom scroll",event);
            MVDTouch.synchronizeTextScroll($(this),$('#top-view'));
            
    });*/
    
    // Create the slider and load versions
    if (MVDTouch.mode === 'remote'){
        // Get version info from server
        $.ajax({
            url: '/joomla/index.php',
            data: {
                option: 'com_mvd',
                view: 'fragment',
                format: 'raw',
                name: MVDTouch.mvdName,
                list: 'y'
            },
            dataType: 'xml',
            success: function(data){
                // load version data from xml values
                // we store 'edition' versions separately from the others
                MVDTouch.data.versions = [];
                $(data).find('version').each(function(i,el){
                    var shortName = $(el).attr('shortname');
                    var versionData = {
                        id: i+1,
                        shortName: shortName,
                        title: $(el).attr('longname'),
                        type: 'MVD'
                    };
                    if (MVDTouch.data.editions.length > 0 && shortName === MVDTouch.data.editions[0]){
                        MVDTouch.data.topEditionVersion = versionData;
                    } else if (MVDTouch.data.editions.length > 1 && shortName === MVDTouch.data.editions[1]){
                        MVDTouch.data.bottomEditionVersion = versionData;
                    } else {
                        MVDTouch.data.versions.push(versionData);
                    }
                });
                $.proxy(MVDTouch.init(), MVDTouch);
            }, 
            error: function(jqXHR, textStatus, errorThrown){
                console.log("Loading version data failed " + textStatus + " " + errorThrown, jqXHR);
                $('#top-view').html("Unable to load MVD (" + textStatus + "): " +  errorThrown);
            }
        });
    } else {
        $.proxy(MVDTouch.init(), MVDTouch);
    }
    // TODO:
    // Scrolling in either one of these views will result in synchronized scrolling of the other view
    // Touching/clicking on an underlined pair pops up a box containing textual narrative which may contain links to selections from other versions from either edition – clicking/touching a link will cause the slider to move to and the contents of the view to morph to the linked version
    // clicking or hovering on the star will change the colour of the lemma and display a pop up note box
});