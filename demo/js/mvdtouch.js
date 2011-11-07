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
    mode: 'local',
    /**
     * The name of the MVD we are working with for the demo
     * @type String
     */
    mvdName: 'tess',
    mvdTitle: 'Tess of the d\'Urbervilles',
    //mvdTitle: 'An Old Mate Of Your Father\'s',
    localPath: 'data/',
    remotePath: 'index.php',
    syncScroll: true,
    BOTTOM: 0,
    TOP: 1,
    getVersionLabelCount: function(){
        var numEditions = this.getEditionCount();
        /* all non-edition versions have two labels (one for each edition group)
         * + each edition version has one label : these appear at the top and 
         * bottom of the slider
         */
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


        var sliderOffset = $('#slider').position().top,
            labelOffset = 5, // adjustment so that labels line up with handles
            sliderHeight = $('#slider-pane').height() - 30,
            numLabels = this.getVersionLabelCount()
            labelSpacing = (sliderHeight+15) / numLabels,
            numEditions = this.getEditionCount();
        $('#slider').height(sliderHeight);
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
                    return (sliderHeight / MVDTouch.getEditionCount() 
            * (index + 1)- labelOffset) + 'px';
                }
            );
            $('.edition-label').css('top',
                function(index){
                    return (index === 0 ? -6 :
            (Math.floor(labelSpacing * numLabels ) - labelOffset - 6))+ 'px';
                    
                }
            );
        }
    },
    onSliderChange: function(event, ui) {
        var totalNum = this.getVersionLabelCount(),
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
            bottomVersion = this.getVersion(bottomVal);

    
        var numVersions = this.data.versions.length,
            numEditions = this.getEditionCount(),
            topEdition = this.data.editions[Math.floor((totalNum - topVal) / numVersions)],
            bottomEdition = this.data.editions[Math.floor((totalNum - bottomVal) / numVersions)];
        

        // load texts, the final arg indicates whether top or bottom view was changed
        this.loadVersions(topVersion, bottomVersion, topEdition, bottomEdition, $(ui.handle).index());


    },
    synchronizeTextScroll: function(viewThatChanged, viewToScroll,idToSync) {
        // check whether views should be synchronized
        if (this.syncScroll){
           if (!idToSync){
                // find the nearest span that has an id to the center of the scrolledView
                var syncPoint = viewThatChanged.scrollTop() + viewThatChanged.height() / 2;
                var syncSpan = viewThatChanged.find('span[id], p[id]').filter(function(){
                    var elTop = this.offsetTop; 
                    return elTop >= syncPoint && elTop <= syncPoint + 50; 
                }).filter(':first');    
                var syncSpanId = syncSpan.attr('id');
            } else {
        syncSpanId=idToSync;
            }

            if (syncSpanId){
              var toScrollSpanId = (syncSpanId.charAt(0) === 'a' ?
         'd' + syncSpanId.substring(1) :
         'a' + syncSpanId.substring(1));
              if (toScrollSpanId){
                var toScroll = viewToScroll.find('#' + toScrollSpanId);
                if (toScroll.length >= 1 && toScroll.offset()){
                   this.syncScroll = false;
                   viewToScroll.animate(
                        {
                            scrollTop: toScroll[0].offsetTop - viewToScroll.height() / 2
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
            
        }
    },
    showExplanatoryNotes: function(view, viewVersion, viewEdition){
        //console.log("show expl. notes", viewVersion);
        for (var n=0; n< MVDTouch.data.explanatoryNotes.length; n++){
            var note = MVDTouch.data.explanatoryNotes[n];
            for (var c = 0; note.context && c < note.context.length; c++){
                var context = note.context[c];
                if (context.version = viewVersion){
        //&& context.edition == viewEdition){

                var targetEl = view.find(context.selector);
                var newText = targetEl.text().replace(context.match,"<span class='nexp-note'>" + context.match + "</span>")
                targetEl.html(newText);
                
                targetEl.find('.nexp-note')
                    .css('text-decoration','underline')
                    .data('note', note)
                    .bind('click touchstart mouseover',function(){
                       var note = $(this).data('note');
                       $(document.createElement("div"))
                                       .appendTo($('#narrative-notes'))
                                       .addClass('exp-dialog')
                       .html(note.body)
                       .dialog(
                           {title: note.title, width: 400}
                       );
                 
                    });
                
            }
        }
    }
    },
    /** Show markers for notes from TEI */
    showMarkers: function(view, version, isTop) {
    // page markers
        view.find('.pb').qtip({
        content: {text: function(){return "p. " + $(this).attr('data-n');}},
            style: {classes:'ui-tooltip-tipsy'},
            position: {my:'center-left',at:'top-right'}
    });
    // attach tooltips for MVD markup
        /*view.find('.added, .deleted, .transposed').qtip({
        content: {
          attr: 'class'
                }, 
        show: {
          event: 'click mouseenter'
                },
        style: {
          classes: 'ui-tooltip-dark ui-tooltip-shadow ui-tooltip-tipsy'
                },
        position: {
          my: 'bottom-center',
          at: 'top-center'
        }
    });
        */

    // remove old instance of button for switching between images/transcripts
    // remember button state as showImage
    var btn = isTop? $('.top-toggle-button') : $('.bottom-toggle-button');
    var showImage = btn.hasClass('image-toggle-button');
    btn.remove();
        MVDTouch.syncScroll = true;

    // singlePanelMode = sliders are on same value (UI only shows the top panel)
        var singlePanelMode = $('.ui-layout-center').layout().state.south.isHidden;
    view.find('.facs').each(function(i,el){
         if (i == 0 && (isTop || !singlePanelMode)){
       // attach button to toggle between image and transcript if there is a facsimile
      var toggle = $(document.createElement('img'))
           .attr('src','images/blank.png')
       .addClass('view-toggle-button')
           .appendTo('.ui-layout-center')
       .toggle(function(){
              view.find('.facsimile').css('display','block');
              view.find('.transcript').css('display','none');
          $(this).addClass('image-toggle-button');
              MVDTouch.syncScroll = false;
        },
            function(){
          view.find('.facsimile').css('display','none');
              view.find('.transcript').css('display','block');
              $(this).removeClass('image-toggle-button');
          MVDTouch.syncScroll = true;
        }
       );

       // add CSS class(es) for button positioning within container
       if (isTop){
         toggle.addClass('top-toggle-button');
       } else {
         toggle.addClass('bottom-toggle-button');
       }
       if (singlePanelMode){
         toggle.addClass('single-toggle-button');
       }
      
       // restore image view if button state was to show image
       if (showImage) {
         toggle.trigger('click');
       }
      }

      // for all facs: create image path for fascimile image from data-src attr
      var src = $(el).attr('data-src');
      $(el).attr('src','/data/' + MVDTouch.mvdName + "/" + version.shortName + src);
    });


    // create margin markers for TEI notes, with popups
        var previousPosition;
        var defaultIndent = 3;
        var indent = defaultIndent;
        $(view).find('.note').each(function(i){
            var note = $(this);
            var newPosition = note.position();
            note.hide();

            // create note marker in margin
            var noteMarker = $(document.createElement('div'))
                .appendTo($(view).find('.transcript'))
                .addClass('noteMarker')
                .html("[" + (i + 1) + "]")
                .css('top', newPosition.top + "px")
                .data('note', note)
                .qtip({
          show: {
            event: 'click mouseenter'
                  },
          style: {
            classes: 'ui-tooltip-dark ui-tooltip-shadow ui-tooltip-tipsy'
                  },
          content: {
            title: function(){
            return $(this).data('note').find('.note-author').clone()
                    },
                    text: function(){
            return $(this).data('note').find('.note-content').clone()
                    }
                  },
          position: {
            my: 'top-left',
            at: 'bottom-right'
          }, 
          events : {
                    // highlight note text on show
                    show: function(event, api){
               // try to find existing target marker
               var myNote = $(event.originalEvent.currentTarget).data('note'); 
               if (myNote && !myNote.data('targetMarker')){
              // if it does not exist, create it
              var targetId = myNote.attr('data-target'),
                  targetMatch = myNote.attr('data-match');
              if (targetId && targetMatch){
                  // create markup around note target
                 try{
                 var target = MVDTouch.markRange(targetId, targetMatch,'noteTarget');
                 // store ref to target marker in node data
                 myNote.data('targetMarker',target);

                  } catch (e){
                 console.log("Unable to find note target",e);
                  }  

              }

               }
               var targetMarker = myNote.data('targetMarker');
               if (targetMarker) targetMarker.css('color','orange');
                    },
                    hide: function(event, api){
                     try{
             var targetMarker = 
             $(event.originalEvent.currentTarget).data('note')
              .data('targetMarker');
             if(targetMarker) targetMarker.css('color','');

                      //.prevAll(':not(:empty):first')
                 } catch (e){
            console.log("Error on note hide",e);
             }
                    }
                  }
                })
          

             if (previousPosition === newPosition.top){
                indent = indent + noteMarker.width();
                noteMarker.css('left', indent + "px");
             } else {
                indent = defaultIndent;
             }
             previousPosition = newPosition.top;
             note
                .css('float','left')
                .css('width','auto')
                .css('height','auto')
                .css('position','absolute')
                .css('top', (newPosition.top + 20)+'px')
                .css('left', newPosition.left+'px');
          
        });
        
    },
    loadVersions: function(topVersion, bottomVersion, topEdition, bottomEdition, updatedViewIndex) {
       function generateVersionPath(thisVersion, otherVersion, isTop) {
            var path;
            if (MVDTouch.mode === 'local'){
                path = MVDTouch.localPath + MVDTouch.mvdName;
                if (thisVersion.type === 'MVD'){
                    
                    if (otherVersion.type === 'MVD' && thisVersion.id !== otherVersion.id){
            path += (isTop ? '/lhs/' : '/rhs/') + thisVersion.id + 'vs';
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
            }
            return path;
        }

        var topPath = generateVersionPath(topVersion, bottomVersion, true),
            bottomPath = generateVersionPath(bottomVersion, topVersion, false);

    //console.log(topVersion, bottomVersion, topPath,bottomPath,topEdition,bottomEdition);
    
        var bottomView = $('#bottom-view').load(bottomPath, function(){
            MVDTouch.showMarkers(bottomView, bottomVersion, false);
            MVDTouch.showExplanatoryNotes(bottomView, bottomVersion.id, 1);
            

        });

    //var bottomView = $('#bottom-view').load("data/oldmate/oldmate.html");

    //$('#bottom-view').load("data/ms_a.html");

        var topView = $('#top-view').load(topPath, function(){
            MVDTouch.showMarkers(topView, topVersion, true);
            MVDTouch.showExplanatoryNotes(topView, topVersion.id, 0);
            if(this.syncScroll){
        // synchronize scroll positions
                if (updatedViewIndex === this.BOTTOM) {
                    this.synchronizeTextScroll(topView, bottomView);
                } else {
                    this.synchronizeTextScroll(bottomView,topView);
                }
        }
        });
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
        var slider = $('#slider').slider({
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
                    .appendTo(labelContainer)
            .bind('click touchstart',function(){
            var values = slider.slider('values');
            slider.slider('values',[values[0],numLabels]);
                });
        }
        if (bottomEdition){
            $(document.createElement('div'))
                    .addClass('edition-label')
                    .text(bottomEdition.title)
                    .appendTo(labelContainer)
            .bind('click touchstart',function(){
            var values = slider.slider('values');
            slider.slider('values',[0,values[1]]);
                });
        }
        // create labels for all non-edition versions
        for (var i = 0; i <= numNonEditionLabels; i++){
            var version = this.data.versions[i % this.data.versions.length];
            $(document.createElement('div'))
                .addClass('version-label')
                .text(version.title)
        .data('sliderIndex',(numLabels - i) - 1)
                .appendTo(labelContainer)
        .bind('click touchstart',function(){
                        console.log("clicked");
            // add a click handler to jump directly to value on slider
            var index = $(this).data('sliderIndex');
            var values = slider.slider('values');
            var newValues;
            if (Math.abs(values[0] - index) > Math.abs(values[1] - index)){
                newValues = [values[0],index];
            } else {
                newValues = [index,values[1]];
            }
                        slider.slider('values',newValues);
        });
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
            
            }
        });

    // map touch events to mouse events to control slider ui
        $('.ui-layout-east').addTouch();

        // Load initial texts
    slider.slider('values',[0,numLabels]);
        this.positionSliderLabels();
    },
    moveSlider: function(sliderHandle, versionId, editionId){
        var slider = $('#slider');
        $('.exp-dialog').dialog("close");
    var values = slider.slider('values');
        var foundId = -1;
        for (var j = 0; foundId < 0 && j < this.data.versions.length; j++){
      if (this.data.versions[j].shortName == versionId){
             foundId = this.data.versions.length - j;
          }
        }
        if (foundId >= 0){
           if (sliderHandle == 0){
        slider.slider('values',[foundId, values[1]]);
       } else {
        slider.slider('values',[values[0],foundId]);
       }
    }
    },
    /** Helper function to create a DOM range within a target element matching the supplied text */
    markRange: function(targetId, targetMatch, cls){
        console.log("finding " + targetId + " " + targetMatch);

        var words = targetMatch.split(" ");
        var matchWord = words[words.length -1];
        var theMatch = $('#'+targetId + ' > *:contains("' + matchWord + '"):first');
        return theMatch;
        
        /*var range = document.createRange(), 
            targetEl = $('#'+targetId);
        // TODO: search targetEl contents to construct range, 
            // there may be other markup inserted by MVD
    
    
        //range.setStart(targetEl.get(0), offsetStart);
            //range.setEnd(targetEl.get(0), offsetEnd);
            */
        var noteTarget = document.createElement("span");
        noteTarget.className = cls;
        //range.surroundContents(noteTarget);
        //return $(noteTarget);
        // find the text node that contains the match
            var completeMatch = $('#'+targetId)
        .andSelf()
        .contents()
            .find (':contains("'+targetMatch+'"):not(.note,.note-content,.note-content > *):first')
    /*      .filter(function(){
              return  !($(this).parents('.note, .note-content').length > 0 ||
                $(this).hasClass('note'));
                 
            })
    */
        //.filter(function(){
        //  if (this.nodeValue && this.nodeValue.indexOf(targetMatch) != -1){
        //    return this.nodeType ===3;
        //  } else return false;
        //});
        if(completeMatch.length > 0){
            console.log("found complete match",completeMatch);
            try{
//            var range = document.createRange(),
//             targetEl = completeMatch.get(0);

//            var offsetStart = completeMatch.text().indexOf(targetMatch), 
//      offsetEnd = offsetStart + targetMatch.length; 
    
//  console.log("create range " + offsetStart + " " + offsetEnd, targetEl);
//            range.setStart(targetEl,offsetStart);
//           range.setEnd(targetEl,offsetEnd);
//            range.surroundContents(noteTarget);
       completeMatch.html(completeMatch.text().replace(targetMatch,
        '<span class="' + cls + '">' + targetMatch + '</span>'));
      return completeMatch.find('.'+ cls);
           } catch (e){
        console.log("Problem",e);
        return theMatch;
           }
        } else {
        console.log("note marker?" , theMatch);
        return theMatch;
    }
    },
    data: {
        // shortnames correspond to shortnames of editions from MVD
        editions: ['Clarendon', 'Penguin'],
        // add version data here if configuring local demo
        topEditionVersion: {
                title: 'Authorial Edition',
                        shortName: 'Clarendon',
                        type: 'MVD',
                        id: 9
    },
        bottomEditionVersion: {
                title: 'Social-text Edition',
                        shortName: 'Penguin',
                        type: 'MVD',
                        id: 10
    },
        explanatoryNotes: [ // containing explanatory narrative
        {
            title: 'I shall soon be back again',
            body: '<div class="narrative"><div>In the genesis of the story in <a href="#" onclick="MVDTouch.moveSlider(0,\'ms\')">manuscript</a> Alec rescued &lsquo;Sue&rsquo; (as Tess was then named) in his gig, where he has on hand the large wicker household spirit-jar of his mother&rsquo;s, from which he forces her to drink. As in the <a href="#" onclick="MVDTouch.moveSlider(0,\'1891\')">1891</a> Tess, she swallows instinctively to avoid the liquid spilling on her best dress. In <a href="#" onclick="MVDTouch.moveSlider(0,\'1891\')">1891</a>, however, Alec is on horse-back, which rules out the unwieldy spirit-jar, and Hardy substitutes instead the small &lsquo;druggist&rsquo;s bottle&rsquo;, which accentuates the elements of Gothic melodrama in the scene, and leaves no doubt that Alec rapes Tess. The few mouthfuls of strong alcohol put an already &lsquo;inexpressibly weary&rsquo; heroine to sleep in the <a href="#" onclick="MVDTouch.moveSlider(0,\'ms\')">ms</a>., but not before she has time to despair of her vulnerability at Alec&rsquo;s hands; when he returns in the dark and puts his face close to her sleeping face, he detects traces of &lsquo;damp about her eyelashes&rsquo; &lsquo;as if she had wept&rsquo;.</div><div>The Tess of <a href="#" onclick="MVDTouch.moveSlider(0,\'1891\')">1891</a> does not weep, presumably because she has been drugged with the cordial.<br/>The removal of the clumsy plot device of the villain with his sleeping drug in <a href="#" onclick="MVDTouch.moveSlider(0,\'1892\')">1892</a> and subsequently throws a veil over the violation, shrouding it in the darkness and silence that rules everywhere in the Chase, and making it impossible in later editions for the reader to make an unequivocal judgement about what happens.</div></div>',
            context: [{
                version: 9,
                edition: 0,
                selector: '.deleted:contains("shall soon be back again"), span[id]:contains("shall soon be back again")',
                match: 'shall soon be back again'
            }]
                },
        {
            title: 'in the hands of the spoiler',
            body: '<div class="narrative">The <a href="#" onclick="MVDTouch.moveSlider(1,\'1891\')">1891</a> Tess is the only edition, furthermore, which contains the clear allusion to rape in the reference to &lsquo;the hands of the spoiler&rsquo; (from the second book of Judges in the Bible). We have no way of knowing whether the paragraph which contains this phrase appeared in the <a href="#" onclick="MVDTouch.moveSlider(0,\'ms\')">MS</a>. (the <a href="#" onclick="MVDTouch.moveSlider(0,\'ms\')">MS</a>. sheets are missing from the end of this episode). What we do know is that Hardy removed it from <a href="#" onclick="MVDTouch.moveSlider(0,\'1892\')">1892</a> along with the druggist&rsquo;s bottle. In place of the &lsquo;sons of the forest&rsquo; who do not have &lsquo;the least inkling that their sister was in the hands of the spoiler&rsquo;, he added a speech to the dialogue of the women who discuss Tess&rsquo;s fate in the fields in chapter xiv. Their conversation in 1891 turns on the remark that &lsquo;&ldquo;&rsquo;Twas a thousand pities that it should have happened to she, of all others&rdquo;&rsquo;. In <a href="#" onclick="MVDTouch.moveSlider(0,\'1892\')">1892</a>, Hardy adds: &lsquo;&ldquo;A little more than persuading had to do wi&rsquo; the coming o&rsquo;t, I reckon. There were they that heard a sobbing one night last year in The Chase; and it mid ha&rsquo; gone hard wi&rsquo; a certain party if folks had come along&rdquo;&rsquo;. Hardy probably introduced his exchange into later editions to counterbalance his removal of the incriminating drug from the scene in the Chase.</div>',
            context: [{
                version: 10,
                edition: 1,
                selector: '.added:contains("hands of the spoiler")',
                match: 'hands of the spoiler'
            }]

        }
        ],
        versions: [
            // all other versions
            
                 {
                        title: 'Manuscript',
                        shortName: 'ms',
                        type: 'MVD',
                        id: 1
                 }, {
                        title: 'Saturday Night in Arcady',
                        shortName: 'SatNight',
                        type: 'MVD',
                        id: 2
                 }, {
                        title: '1891',
                        shortName: '1891',
                        type: 'MVD',
                        id: 3
                 }, {
                        title: '1892',
                        shortName: '1892',
                        type: 'MVD',
                        id: 4
                 }, {
                        title: '1895',
                        shortName: '1895',
                        type: 'MVD',
                        id: 5
                 }, {
                        title: '1900',
                        shortName: '1900',
                        type: 'MVD',
                        id: 6
                 }, {
                        title: '1902',
                        shortName: '1902',
                        type: 'MVD',
                        id: 7
                 }, {
                        title: '1912',
                        shortName: '1912',
                        type: 'MVD',
                        id: 8
                 }
             
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
    $('#header-left').text(MVDTouch.mvdTitle);
    // synchronize scrolling between text views
    $('#top-view').scroll(function(event){
        //console.log("top scroll",event)        
        MVDTouch.synchronizeTextScroll($(this),$('#bottom-view'));
    });

    $('#bottom-view').scroll(function(event) {
            //console.log("bottom scroll",event);
            MVDTouch.synchronizeTextScroll($(this),$('#top-view'));
            
    });
    
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
        console.log("loading data from local files");
        $.proxy(MVDTouch.init(), MVDTouch);
    }
    // TODO:

    // Touching/clicking on an underlined pair pops up a box containing textual narrative which may contain links to selections from other versions from either edition – clicking/touching a link will cause the slider to move to and the contents of the view to morph to the linked version

});