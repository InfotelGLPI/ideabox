<?php
use Glpi\Event;
include('../../../../inc/includes.php');
header('Content-Type: text/javascript');

include ('diacritics.js');

?>

var root_id_doc = "<?php echo PLUGIN_IDEABOX_WEBDIR; ?>";

$(function() {
   var list = [];

   // prepapre options for fuzzy lib
   var fuzzy_options = {
      pre: "<b>",
      post: "</b>",
      extract: function(el) {
          return el.title;
      }
   };

   // when the shortcut for fuzzy is called
   // $(document).on('keyup', null, 'alt+ctrl+g', function() {
   //    trigger_fuzzy();
   // });

   // when a key is pressed in fuzzy input, launch match
   $(document).on('click', ".id-home-trigger-fuzzy", function(key) {
      trigger_homesearch_fuzzy();
   });


   var fuzzy_started = false;
    var trigger_homesearch_fuzzy = function() {
        // remove old fuzzy modal
        //removeFuzzy();

        // retrieve current menu data
        $.getJSON(root_id_doc+'/ajax/fuzzysearch.php', {
            'action': 'getList',
        }, function(data) {
            list = data;

            // start fuzzy after some time
            setTimeout(function() {
                if ($("#fuzzysearch .results li").length == 0) {
                    startFuzzy();
                }
            }, 100);
        });

        // focus input element
        $("#fuzzysearch input").trigger("focus");

        // don't bind key events twice
        if (fuzzy_started) {
            return;
        }
        fuzzy_started = true;

        // general key matches
        $(document).on('keyup', function(key) {
            switch (key.key) {
                case "Escape":
                    $("#fuzzysearch .results").empty();
                    $(".id-home-trigger-fuzzy").val('');
                    break;

                case "ArrowUp":
                    selectPrev();
                    break;

                case "ArrowDown":
                    selectNext();
                    break;

                case "Enter":
                    // find url, if one selected, go for it, else try to find first element
                    var url = $("#fuzzysearch .results .active a").attr('href');
                    if (url == undefined) {
                        url = $("#fuzzysearch .results li:first a").attr('href');
                    }
                    if (url != undefined) {
                        document.location = url;
                    }
                    break;
            }
        });

        $(document).on('click', function(key) {
            $("#fuzzysearch .results").empty();
            $(".id-home-trigger-fuzzy").val('');
        });

        // when a key is pressed in fuzzy input, launch match
        $(document).on('keyup', "#fuzzysearch input", function(key) {
            if (key.key != "Escape"
                && key.key != "ArrowUp"
                && key.key != "ArrowDown"
                && key.key != "Enter") {
                startFuzzy();
            }
        });
    };


   var startFuzzy = function() {

      // retrieve input
      var input_text = $("#fuzzysearch input").val();
      var input_strict = $("#fuzzy-strict").val();
      if(input_strict == 1){
           input_text = "\'"+input_text;
      }

      //clean old results
      $("#fuzzysearch .results").empty();

      // launch fuzzy search on this list
      //var results = fuzzy.filter(input_text, list, fuzzy_options);
      const options = {
          isCaseSensitive: false,
         // includeScore: false,
          shouldSort: false,
         // includeMatches: false,
         // findAllMatches: false,
          minMatchCharLength: 2,
         // location: 0,
         // threshold: 0.6,
         // distance: 100,
         includeScore: false,
         ignoreLocation: true,
         useExtendedSearch: true,
         // ignoreFieldNorm: false,
         // fieldNormWeight: 1,
         getFn: (obj, path) => {
              var value = Fuse.config.getFn(obj, path);
              return removeDiacritics(value);
          },
         keys: [
            "title",
            "comment",
         ]
      };
      //console.log(list);
      const fuse = new Fuse(list, options);

      var results = fuse.search(removeDiacritics(input_text));
      var sorted_results = results.sort((a, b) => a['item'].order.localeCompare(b['item'].order) || a['item'].title.localeCompare(b['item'].title));
       var target = '_blank';
//
////      const searchWrapper = query => {
//         if (!query) return fuse.getIndex().records.map(({ $: item, i: idx }) => ({ idx, item }));
//         results =  fuse.search(query);
//      };
//// Change the pattern
//      console.log(results);



      // append new results
       sorted_results.map(function(el) {
         //console.log(el);
           if ( el.item.targets && el.item.targets.length > 0) {
               var finaltitle = '(' + el.item.targets + ') ' + el.item.title; //el.item.type + " > " +
           } else {
               var finaltitle = el.item.title; //el.item.type + " > " +
           }
         //$("#fuzzysearch .results")
         //   .append("<li class='list-group-item list-group-item-primary'><i class='fa-1x fas "+el.item.icon+"' style=\"font-family:'Font Awesome 5 Free', 'Font Awesome 5 Brands';\"></i> <a target='"+el.item.target+"' href='"+ el.item.url+"'>"+finaltitle+"</a><div><i style='color: #CCC;'>"+el.item.comment+"</i></div></li>");
           $("#fuzzysearch .results")
               .append("<li style='background-color: #FFF;'><a target='"+el.item.target+"' href='"+ el.item.url+"' class='list-group-item list-group-item-action flex-column align-items-start'>" +
                   "<div class='d-flex w-100 justify-content-between'>" +
                   "<h5 class='mb-1' style='font-weight: inherit;'><span class='badge badge-primary badge-pill'><i class='"+el.item.icon+"'>" +
                   "</i></span> " + finaltitle +
                   "</h5></div>" +
                   "<p class='mb-1'><i style='color: #9E9C9C;'>"+el.item.comment+"</i></p>" +
                   "</a></li>");
      });

      selectFirst();
   };



   /**
    * Clean generated Html
    */
   var removeFuzzy = function() {
      $("#fuzzysearch").remove();
   };

   /**
    * Select the first element in the results list
    */
   var selectFirst = function() {
      $("#fuzzysearch .results li:first()").addClass('active');
      scrollToSelected();
   };

   /**
    * Select the last element in the results list
    */
   var selectLast = function() {
      $("#fuzzysearch .results li:last()").addClass('active');
      scrollToSelected();
   };

   /**
    * Select the next element in the results list.
    * If no selected, select the first.
    */
   var selectNext = function() {
      if ($("#fuzzysearch .results .active").length == 0) {
         selectFirst();
      } else {
         $("#fuzzysearch .results .active:not(:last-child)")
            .removeClass('active')
            .next()
            .addClass("active");
         scrollToSelected();
      }
   };

   /**
    * Select the previous element in the results list.
    * If no selected, select the last.
    */
   var selectPrev = function() {
      if ($("#fuzzysearch .results .active").length == 0) {
         selectLast();
      } else {
         $("#fuzzysearch .results .active:not(:first-child)")
            .removeClass('active')
            .prev()
            .addClass("active");
         scrollToSelected();
      }
   };

   /**
    * Force scroll to the selected element in the results list
    */
   var scrollToSelected = function() {
      var results = $("#fuzzysearch .results");
      var selected = results.find('.active');

      if (selected.length) {
         results.scrollTop(results.scrollTop() + selected.position().top - results.height()/2 + selected.height()/2 - 25);
      }
   };
});
