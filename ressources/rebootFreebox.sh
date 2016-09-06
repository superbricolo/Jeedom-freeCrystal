#!/bin/sh
#
# Script bash permettant de redemarrer la Freebox boitiers ADSL & HD
# Par Kidou d'apres une idee de Kysic (Forum Ubuntu-fr)

# Code telecommande accessible dans le menu
# "Informations generales" de la freebox hd
CODE_FREEBOX=$2

# Identifiant du boitier HD
ID_BOITIER=$2
HOST="hd${ID_BOITIER}.freebox.fr"

# Simule un appui sur la touche $1
# Simule un appui long si $2 vaut "long"
# Liste keys :
#     power : la touche rouge on/off
#     list : la touche d'affichage de la liste des chaines entre power et tv
#     tv : la touche verte TV de commutation peritel.
#     0 a 9 : les touches 0 a 9
#     back : la touche jaune en dessous du 7
#     swap : la touche en dessous du 9
#     info, mail, help, pip : les touches bleues a droite des numŽros de chaine
#     epg, media, options : fonctionnalites "secondaires" de ces memes touches
#     vol_inc, vol_dec : volume+ et volume-
#     prgm_inc, prgm_dec : program+ et program-
#     ok : touche OK
#     up, right, down, left : les touches directionnelles entourant le OK
#     mute : la touche de mise en sourdine
#     home : la touche free
#     rec : la touche d'enregistrement
#     bwd : la touche de retour en arriere (<<)
#     prev : la touche "precedent" (|<<)
#     play : la touche lecture/pause
#     fwd : la touche d'avance rapide (>>)
#     next : la touche "suivant" (>>|)
#     red : le bouton rouge (B)
#     green : le bouton vert (A)
#     yellow : le bouton jaune (Y)
#     blue : le bouton bleu (X)

usage(){
   echo  "Usage : $0 <box>"
   echo  "box :"
   echo  "             adsl : redemarre le boitier adsl"
   echo  "             hd   : redemarre le boitier hd - reset les pages et curseur"
   echo  ""
}

# Forge et lancement de la commande wget
appui() {
   if [ "$#" -eq 1 ]
   then
      isLong=false
   else
      if [ "$#" -eq 2 ]
      then
         isLong=true
      else
         echo "Usage : appui <key> [long]"
         return 2
      fi
   fi
   sleep 1 && wget -q -O /dev/null "http://${HOST}/pub/remote_control?code=${CODE_FREEBOX}&key=${1}&long=${isLong}"
}

# reste sur la première ligne si première colonne grace au double bouton Canal+
gopremiereligne(){
   # se déplace vers le bas par la droite, immobile si TV
   appui right && appui down && appui left
   appui right && appui down && appui left
   appui right && appui down && appui left
}

# se place sur la seconde ligne
gosecondeligne(){
   # cherche première ligne pour chacune des 4 colonnes
   gopremiereligne && appui left
   gopremiereligne && appui left
   gopremiereligne && appui left
   gopremiereligne
   # passe à la seconde ligne par en bas à cause du double bouton Canal+
   appui up && appui up
}

# va dans le menu Param (delay aggrandi pour chargement plus long TVperso et Replay)
goparam(){
   # se place sur la seconde ligne
   gosecondeligne
   # cherche le menu Replay (le seul à ne pas sortir via Red)
   appui ok && sleep 2 && appui red && appui left
   appui ok && sleep 2 && appui red && appui left
   appui ok && sleep 2 && appui red && appui left
   appui ok && sleep 2 && appui red && sleep 5 && appui home
   # ouvre le menu Param
   appui left && appui left && appui down && appui ok
   # se place sur Affichage
   appui right && appui right && appui right && appui up
   appui right && appui right && appui right && appui up
   appui right && appui right && appui right && appui up
   appui right && appui right && appui right && appui up
}

# Relance la Box ADSL
relanceadsl(){
   # ouvre le menu Param/Réseau
   appui home && goparam && appui right && appui right && appui ok
   # Redémarrage de la Box ADSL
   appui down && appui ok
   # retour et mise en veille HD
   appui red & appui red && appui red && appui power && sleep 10
}

# Relance la Box HD
relancehd(){
   # ouvre le menu Param/Général
   appui home && goparam && appui right && appui ok
   # Redémarrage de la Box HD
   appui down && appui ok
   # retour et mise en veille HD
   appui red & appui red && appui red && appui power && sleep 10
}

if [ -z $CODE_FREEBOX ]
then
   echo "Erreur : la variable CODE_FREEBOX n'est pas configure\n"
   echo "Boitier HD : Menu Parametres -> Informations generales\n"
   exit 2
fi

case $1 in
adsl)
   # on exécute 2 fois car dans l'un des 2 cas la box HD sera éteinte
   relanceadsl
   relanceadsl
   ;;
hd)
   # on exécute 2 fois car dans l'un des 2 cas la box HD sera éteinte
   relancehd
   relancehd
   ;;
*)
   usage
   exit 1
   ;;
esac
exit 0