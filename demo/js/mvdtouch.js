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
    getLabelCount: function(){
        return this.data.versions.length * this.getEditionCount() - 1;
    },
    getEditionCount: function(){
        // Assume a single unnamed edition if no edition titles were specified in config
        return Math.max(1,this.data.editions.length);
    },
    positionSliderLabels: function(){
        var labelOffset = 5, // adjustment so that labels line up with handles
            sliderHeight = $('#slider').height(),
            labelSpacing = sliderHeight / this.getLabelCount();
        $('.version-label').css('top',
            function(index){
                return (Math.floor(labelSpacing * index) - labelOffset) + 'px';
            }
        );
        $('.slider-label-sep').css('top',
            function(index){
                return (sliderHeight / MVDTouch.getEditionCount() * (index + 1)- labelOffset) + 'px';
            }
        );
    },
    onSliderChange: function(event, ui) {
        //console.log("onsliderchange",ui)
        function generateVersionPath(thisVersion, otherVersion, isTop) {
            var path;
            if (MVDTouch.mode === 'local'){
                path = MVDTouch.localPath + MVDTouch.mvdName;
                if (thisVersion.type === 'MVD'){
                    path += (isTop ? '/lhs/' : '/rhs/') + thisVersion.shortName + 'vs';
                    if (otherVersion.type === 'MVD' && thisVersion.shortName !== otherVersion.shortName){
                        path += otherVersion.shortName + '.html';
                    } else {
                       //other version is same or image, use text path, we won't be comparing
                        path += "/texts/" + thisVersion.shortName + '.html';
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
                    console.log("remote path is " + path);
            }
            return path;
        }
        var numVersions = this.data.versions.length,
            totalNum = this.getLabelCount(),
            bottomVal = ui.values[this.BOTTOM],
            topVal = ui.values[this.TOP];
        
        // If the user has moved both slider handles to the same value, show one pane
        // Checking for originalEvent ensures we only move handles after a mouse event
        if (topVal === bottomVal && event.originalEvent){
            // hide one view
            $('div.ui-layout-center').layout().hide('south');
        } else {
            // show both views
            $('div.ui-layout-center').layout().show('south');
        }
        
        // slider min value is at bottom, but we want value relative from top, so adjust by subtraction
        bottomVal = (totalNum - bottomVal);
        topVal = (totalNum - topVal);
        
        // look up version ids from config to construct paths to texts
        var topVersion = this.data.versions[topVal % numVersions],
            bottomVersion = this.data.versions[bottomVal % numVersions],
            topPath = generateVersionPath(topVersion, bottomVersion, true),
            bottomPath = generateVersionPath(topVersion, bottomVersion, false);

        // load texts, the final arg indicates whether top or bottom view was changed
        this.loadVersions(topPath, bottomPath, $(ui.handle).index());
        // TODO: load explanatory notes for selected edition(s)
        var topEdition = this.data.editions[Math.floor(topVal / numVersions)];
        var bottomEdition = this.data.editions[Math.floor(bottomVal / numVersions)];
        console.log("top is from " + 
            Math.floor(topVal / numVersions),
            topEdition, "bottom is from " + 
            Math.floor(bottomVal / numVersions),bottomEdition );
    },
    synchronizeTextScroll: function(viewThatChanged, viewToScroll) {
        console.log("synchronize text scroll " + this.syncScroll);
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
            console.log(syncSpan.text());
            if (syncSpanId){
              var toScrollSpanId = (syncSpanId.charAt(0) === 'a' ? 'd' + syncSpanId.substring(1) : 'a' + syncSpanId.substring(1));
              var toScroll = viewToScroll.find('#' + toScrollSpanId);
              console.log(document.getElementById(toScrollSpanId));
              console.log(syncPoint + " " + syncSpanId + " " + toScrollSpanId, toScroll);
              
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
        var numLabels = this.getLabelCount(),
            labelContainer = $('#version-labels'),
            numEditions = this.data.editions.length;
        for (var i = 0; i <= numLabels; i++){
            $(document.createElement('div'))
                .addClass('version-label')
                .text(this.data.versions[i % this.data.versions.length].title)
                .appendTo(labelContainer);
        }
        $('#slider').slider({
            orientation: 'vertical',
            max: numLabels,
            values: [0, numLabels], 
            step: 1,
            animate: true,
            change: $.proxy(this.onSliderChange, this)
        });
        // Create edition label(s), and separators if this is a multi-edition demo
        // Separators are added between any number of editions, 
        // but editon labels only shown for first and last from config
        if (numEditions > 0){
            $('#top-edition-label').text(this.data.editions[0].title);
        }
        if (numEditions > 1){
            $('#bottom-edition-label').text(this.data.editions[numEditions - 1].title);
            for (var j = 1; j < numEditions; j++){
                $(document.createElement('hr'))
                    .addClass('slider-label-sep')
                    .appendTo(labelContainer);
            }
        }
        this.positionSliderLabels();
        // TODO:(use slider change handler to init)
        this.loadVersions('data/tess/lhs/MSvs1912.html','data/tess/rhs/MSvs1912.html');
    },
    data: {
        editions: [
            {
                title: 'Authorial Edition'
            },{
                title: 'Social-text Edition' 
            }
        ],
        // default version data (for local mode) - will be overwritten on document load if mode is remote
        versions: [
            {
                title: 'Manuscript',
                shortName: 'MS',
                type: 'MVD'
            }/*,{
                title: 'Graphic', 
                type: 'image',
                id: 'G'
            },{
                title: 'Harper\'s Bazar', 
                type: 'MVD',
                id: 'HB'
            }*/,{
                title: '1891', 
                type: 'MVD',
                shortName: '1891'
            },{
                title: '1892', 
                type: 'MVD',
                shortName: '1892'
            }/*,{
                title: '1895',
                type: 'MVD',
                id: '1895'
            },{
                title: '1900', 
                type: 'MVD',
                id: '1900'
            },{
                title: '1902',
                type: 'MVD',
                id: '1902'
            }*/,{
                title: '1912',
                type: 'MVD',
                shortName: '1912'
            }/*,{
                title: '1920',
                type: 'MVD',
                id: '1920'
            }*/
        ]       
    }
}

$(document).ready(function(){
    // Configure page layout
    $('body').layout({
        east: {
            spacing_open: 2,
            size: '100',
            closable: false,
            resizable: false
        },
        north: {
            spacing_open: 0,
            size: '20',
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
   $('div.ui-layout-east').layout({
        applyDefaultstyles: true,
        center: {
            paneSelector: '#slider-pane',
            onresize: $.proxy(MVDTouch.positionSliderLabels, MVDTouch)
        },
        east: {
            paneSelector: '#version-labels',
            size: 75,
            closable: false,
            resizable: false
        
        },
        south : {
            paneSelector: '#bottom-edition-label',
            resizable: false,
            closable: false
        },
        north: {
            paneSelector: '#top-edition-label',
            resizable: false,
            closable: false
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
                MVDTouch.data.versions = [];
                $(data).find('version').each(function(i,el){
                    MVDTouch.data.versions[i] = {
                        id: i,
                        shortName: $(el).attr('shortname'),
                        title: $(el).attr('longname'),
                        type: 'MVD'
                    }
                });
                $.proxy(MVDTouch.init(), MVDTouch);
            }, 
            error: function(jqXHR, textStatus, errorThrown){
                console.log("Loading version data failed " + textStatus + " " + errorThrown, jqXHR);
                // fallback to local data
                MVDTouch.mode = 'local';
                $.proxy(MVDTouch.init(), MVDTouch);
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