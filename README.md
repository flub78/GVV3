# GVV3
Gestion vol à voile version 3

Ce projet est la version 3 de GVV. C'est une réécriture complete. Il est basé sur le projet CITEMPLATE qui doit être installé avant lui.

Il permet 
* la gestion des pilotes, des machines, des heures de vol
* La facturation
* La comptabilité du club
* La sortie de statistiques
* Le suivie des formations
* La gestion d'un calendrier de présence


Evolutions par rapport à la version 2
* Il est basé sur CodeIgniter 3.0
* Il utilise bootstrap afin d'etre utilisable sur téléphone, tablette et grands écrans
* Il offre une meilleure couverture de test.
* Le module d'identification qui provoquait des déconnexions intempestives a été remplacé
* Il offre des API REST pour l'interconnection avec d'autres programmes. Il est inteconnecté avec glidernet pour la saisie automatique des planches et il est prévu de l'interconnecter avec les serveurs de la FFVV

Liens
-----

Lien vers la version 2: http://projets.developpez.com/projects/gvv/wiki/Documentation_utilisateur

Git repo: https://github.com/flub78/GVV3

## Installation

### Pré-requis
    * Un serveur WEB, par exemple Apache
    * Un serveur Mysql, (ou autre base de données supporté par CodeIgniter 3)
    * PHP 5.5 ou supérieur
    
### Etapes
    * Extraire le projet citemplate dans le répertoire de votre serveur WEB (/var/www/html sous Ubuntu). GVV3 peut aussi être installé en https.
    * Extraire ce projet dnas le même répertoire. (Certain fichiers sont remplacés)
    * Créer une base de données et un utilisateur
    * Editer et adapter les fichiers application/config/config.php et application/config/database.php
    
    * Lancer le programme, suivez ses indications sur les droits des répertoires
    * Il créera lui même 
    
#### Installation sur une base GVV2 active

    Il est possible de faire fonctionner le programme sur une base GVV2 active. Référencez juste la base et l'utilisateur GVV2.
    Attention des tables suplémentaries vont être crées dans la base. Elle ne seront pas sauvegardées par GVV2. La compatibilité n'est assurée qu'entre la dernière version de GVV2 et les premières version de GVV3. Les évolutions de GVV3 pour faire migrer la base de façon incompatible avec GVV3.
    
    C'est une configuration de dévelopement et de test
    
#### Installation à partir d'une sauvegarde de GVV2

    Si vous copiez la denière sauvegarde de GVV2 sous install/database.sql, votre sauvegarde sera utilisée pour peupler 
    la base de données. Attention cela ne fonctionne qu'avec la dernière version de GVV2. Si vous n'êtes pas à jour,
    effectuez d'abord la mise à jour.    

#### Mise à jour

    * Sauvegardez votre base et vos fichiers de configuration
    * Copiez juste la nouvelle version du programme (d'abord citemplate, puis GVV3) dans le répertoire de votre serveur WEB.
    * Fusionnez vos ficheir de configuration avec la version du programme. Ou ajoutez les nouveaux paramètres s'il y en a dans vos fichiers de configuration.
    * Relancez le programme, la migration de la base de données est automatique.