# Principes de développement

## ERD Entity Relation Diagram

Il est recommandé d'utiliser Workbench pour générer et documenter le modèle de base de données.

* Note GVV3 devant être compatible avec GVV2, sa première version de base de données sera la même que celle de GVV2. Eventuellement du refactoring sera effectué dans GVV2 avant la première version de GVV3.

## REST interface

GVV3 sera doté d'une interface REST qui permettra :

* D'effectuer toutes les consultations et modifications du model
* d'indentifier les utilisateurs
* qui controlera la cohérence du model et rejetera les opérations illégales
