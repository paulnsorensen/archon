<?php
abstract class Creators_AdminSection
{
    public function outputInterface()
    {
        if($this->Type == 'creatorsrelation')
        {
            $this->outputCreatorsRelationInterface();
        }
        else
        {
            $this->callOverridden();
        }
    }



    public function outputCreatorsRelationInterface()
    {
        global $_ARCHON;

        if($_ARCHON->AdministrativeInterface->Object->ID)
        {
            call_user_func(array($_ARCHON->AdministrativeInterface->Object, 'dbLoadCreators'));
            $arrCreators = $_ARCHON->AdministrativeInterface->Object->Creators;
            $_ARCHON->AdministrativeInterface->Object->PrimaryCreatorID = ($_ARCHON->AdministrativeInterface->Object->PrimaryCreator) ? $_ARCHON->AdministrativeInterface->Object->PrimaryCreator->ID : 0;
            //$arrPrimaryCreators = $_ARCHON->AdministrativeInterface->Object->PrimaryCreators;
        }
        else
        {
           $arrCreators = array();
           //$arrPrimaryCreators = array();
        }


        //TODO make this a reload field and make the js use delegation binds
        $this->insertRow('primary_creator')->insertSelect('PrimaryCreatorID', $arrCreators);

       ?>

<script type='text/javascript'>
   /* <![CDATA[ */
   $(function () {
      $('#creatorsRelatedCreatorIDs').bind('relationchange', function () {

         var arrOpts = $('#creatorsRelatedCreatorIDs>*');
         var arrNotToRemove = [];
         var arrToAdd = [];

         //This should keep the (select one) value in the select
         arrNotToRemove[0] = true;

         $(arrOpts).each(function (i, opt) {
            var v = $(opt).val();
            var toAdd = true;
            
            $('select[name="PrimaryCreatorID"]>*').each(function (j, currOpt) {
               if(toAdd) { //if found, make looping faster
                  if($(currOpt).val() == v){
                     arrNotToRemove[j] = true;
                     toAdd = false;
                  }
               }
            });

            if(toAdd) {
               arrToAdd.push(i);
            }
         });

         var selectFirst = false;

         for (i=$('select[name="PrimaryCreatorID"]>*').length-1; i>=0; i--){
            if(!arrNotToRemove[i]) {
               if(!selectFirst && $('select[name="PrimaryCreatorID"]>*').eq(i).attr('selected')){
                  selectFirst = true; //the selected primary creator has been removed, so select the first listed
               }
               $('select[name="PrimaryCreatorID"]>*').eq(i).remove();
            }
         }
         
         if($('select[name="PrimaryCreatorID"]>*').length == 1){ //only the (select one) option is there
            selectFirst = true;
         }

         $.each(arrToAdd, function(i, val) {
            $('select[name="PrimaryCreatorID"]').append($('#creatorsRelatedCreatorIDs>*').eq(val).clone());
         });
         arrNotToRemove = [];
         arrToAdd = [];

         // The second node is actually the first listed creator, since the first one is the (select one)
         if(selectFirst){
            $('select[name="PrimaryCreatorID"]>*').eq(1).attr('selected','selected');
         }
      });
   });
   /* ]]> */
</script>
        <?php

        $this->outputRelationInterface();
    }
}

//$_ARCHON->setMixinMethodParameters('AdminSection', 'Creators_AdminSection', 'outputInterface', NULL, MIX_OVERRIDE);

//$_ARCHON->mixClasses('AdminSection', 'Creators_AdminSection');

?>
