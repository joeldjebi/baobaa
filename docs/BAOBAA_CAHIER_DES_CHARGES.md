# BAOBAA - Cahier des charges fonctionnel et ligne directrice produit

## 1. Vision du produit

**BAOBAA** est une plateforme web de reservation d'espaces evenementiels visant a devenir le hub panafricain francophone de reference pour trouver, comparer, reserver et payer des salles ou espaces adaptes aux evenements.

**Baseline :** "La ou chaque evenement prend racine"

BAOBAA connecte trois familles d'utilisateurs :

- les clients qui cherchent un lieu fiable pour organiser un evenement ;
- les proprietaires qui souhaitent publier et rentabiliser leurs espaces ;
- l'equipe BAOBAA, incluant le **SAP** (Super Admin Proprietaire), qui controle la plateforme, les commissions, les abonnements, la qualite et les revenus.

## 2. Plateformes de reference et inspiration

References analysees :

- https://www.eventsinminutes.com/
- https://www.eventsinminutes.com/venues?where=San+Francisco%2C+CA
- https://www.eventsinminutes.com/venues/oakland-ca/handsome-meeting-room-for-celebrations-oakland

Elements a retenir :

- une page d'accueil centree sur la recherche rapide ;
- des categories visibles des le premier ecran ;
- un listing avec filtres, cartes, prix, capacite et localisation ;
- une fiche detail tres structuree : photos, apercu, inclusions, commodites, localisation, disponibilites, regles, FAQ et prix ;
- une promesse de confiance : prestataires verifies, prix transparents, disponibilite et reservation simplifiee.

Adaptation BAOBAA :

- remplacer le positionnement US par une approche Afrique francophone ;
- privilegier les villes, devises, moyens de paiement et usages locaux ;
- mettre en avant la verification des espaces, la reservation avec acompte, WhatsApp, Mobile Money et la confiance terrain ;
- construire une identite plus chaleureuse, premium et culturelle, inspiree du baobab, de l'ancrage et du rassemblement.

## 3. Objectifs principaux

### Objectifs clients

- Trouver rapidement un espace evenementiel selon une ville, une date, un budget, une capacite et un type d'evenement.
- Verifier la disponibilite d'un espace.
- Consulter les photos, tarifs, conditions, equipements et avis.
- Reserver et payer le montant de reservation defini par le proprietaire.
- Recevoir une confirmation claire et suivre sa reservation.

### Objectifs proprietaires

- Creer un compte proprietaire.
- Publier une ou plusieurs salles ou espaces.
- Definir les tarifs, disponibilites, conditions de reservation et montant de reservation.
- Recevoir et gerer les demandes de reservation.
- Suivre les paiements, commissions, reversements et performances.
- Souscrire a un abonnement si le modele de la plateforme l'exige.

### Objectifs SAP et equipe BAOBAA

- Valider les comptes proprietaires et les espaces publies.
- Definir le modele economique : commission, abonnement, ou commission + abonnement.
- Suivre les reservations, paiements, commissions et reversements.
- Gerer les litiges, signalements, remboursements et annulations.
- Administrer les utilisateurs, contenus, villes, categories, parametres et droits d'acces.

## 4. Perimetre fonctionnel MVP

Le MVP doit permettre de lancer une premiere version exploitable, sans surcharger le produit.

Fonctionnalites indispensables :

- inscription et connexion client ;
- inscription et connexion proprietaire ;
- tableau de bord proprietaire ;
- creation et modification d'espaces ;
- validation admin des espaces avant publication ;
- recherche d'espaces par ville, type, date, capacite et budget ;
- page listing avec filtres ;
- fiche detail d'un espace ;
- calendrier de disponibilite ;
- demande de reservation ou reservation directe selon le mode choisi ;
- paiement du montant de reservation ;
- calcul de commission BAOBAA ;
- tableau de bord SAP ;
- gestion des abonnements proprietaires ;
- notifications email et/ou WhatsApp selon faisabilite ;
- pages legales et FAQ.

Fonctionnalites post-MVP :

- assistant intelligent de planification d'evenement ;
- systeme de messagerie interne avance ;
- prestataires additionnels : traiteurs, decoration, sonorisation, photo, video, securite ;
- paiement fractionne ;
- calendrier synchronisable ;
- application mobile ;
- programme de mise en avant sponsorisee ;
- verification terrain avec badge avance ;
- API publique ou partenaire.

## 5. Acteurs et roles

### SAP - Super Admin Proprietaire

Role le plus eleve. Il represente le proprietaire de BAOBAA.

Permissions :

- acces total a la plateforme ;
- gestion des administrateurs ;
- definition des commissions globales ;
- creation des plans d'abonnement ;
- activation ou desactivation du modele commission/abonnement ;
- gestion des moyens de paiement ;
- validation finale des espaces sensibles ;
- suivi des revenus globaux ;
- consultation des journaux d'activite ;
- gestion des litiges majeurs.

### Administrateur BAOBAA

Membre interne charge de l'operationnel.

Permissions :

- valider ou rejeter les espaces ;
- moderer les contenus ;
- gerer les utilisateurs ;
- traiter les signalements ;
- assister les clients et proprietaires ;
- consulter les reservations et paiements selon droits.

### Proprietaire d'espace

Utilisateur qui publie et gere des espaces.

Il peut etre :

- une entreprise ;
- une organisation ;
- un hotel ;
- une salle de spectacle ;
- un particulier ;
- une mairie ou institution ;
- un gestionnaire immobilier.

Permissions :

- creer et modifier ses espaces ;
- definir prix, disponibilites et conditions ;
- accepter ou refuser les demandes si le mode est "sur demande" ;
- consulter les reservations ;
- consulter ses paiements et reversements ;
- souscrire ou renouveler un abonnement ;
- repondre aux clients.

### Client / Organisateur

Utilisateur qui reserve un espace.

Permissions :

- rechercher un espace ;
- consulter les fiches detail ;
- verifier la disponibilite ;
- reserver ;
- payer ;
- annuler selon les conditions ;
- laisser un avis apres l'evenement ;
- contacter le proprietaire ou le support.

## 6. Entites principales

### Utilisateur

Champs principaux :

- nom ;
- prenom ;
- email ;
- telephone ;
- mot de passe ;
- type de compte ;
- statut ;
- date de verification ;
- derniere connexion.

### Role

Exemples :

- SAP ;
- admin ;
- moderateur ;
- support ;
- proprietaire ;
- client.

### Profil proprietaire

Champs principaux :

- type de proprietaire ;
- nom commercial ;
- raison sociale ;
- piece d'identite ou document entreprise ;
- telephone WhatsApp ;
- pays ;
- ville ;
- statut de verification ;
- compte de paiement pour reversement.

### Espace

Champs principaux :

- proprietaire ;
- nom ;
- slug ;
- description courte ;
- description detaillee ;
- type d'espace ;
- ville ;
- quartier ;
- adresse ;
- coordonnees GPS ;
- capacite minimale ;
- capacite maximale ;
- surface ;
- statut de publication ;
- statut de verification ;
- note moyenne ;
- nombre d'avis.

### Type d'espace

Exemples :

- salle de mariage ;
- salle de conference ;
- salle de spectacle ;
- rooftop ;
- jardin ;
- restaurant privatisable ;
- hotel ;
- espace seminaire ;
- galerie ;
- lounge ;
- terrain evenementiel ;
- espace culturel.

### Equipement

Exemples :

- climatisation ;
- parking ;
- Wi-Fi ;
- sono ;
- projecteur ;
- scene ;
- loges ;
- cuisine ;
- mobilier ;
- groupe electrogene ;
- securite ;
- toilettes ;
- acces PMR ;
- ecran ;
- micro ;
- lumiere evenementielle.

### Media

Champs principaux :

- espace ;
- type : image, video, plan ;
- url ;
- position ;
- image principale ;
- statut de moderation.

### Disponibilite

Champs principaux :

- espace ;
- date ;
- heure de debut ;
- heure de fin ;
- statut : disponible, bloque, reserve ;
- motif de blocage ;
- recurrence eventuelle.

### Tarif

Champs principaux :

- espace ;
- type : heure, demi-journee, journee, soiree, week-end, forfait ;
- prix ;
- devise ;
- duree minimale ;
- duree maximale ;
- nombre minimal d'invites ;
- nombre maximal d'invites ;
- conditions.

### Reservation

Champs principaux :

- client ;
- espace ;
- proprietaire ;
- date evenement ;
- heure debut ;
- heure fin ;
- nombre d'invites ;
- type d'evenement ;
- montant total estime ;
- montant de reservation ;
- statut : brouillon, en attente, confirmee, refusee, annulee, terminee ;
- mode de reservation : instantanee ou sur demande ;
- date d'expiration de demande ;
- commentaires client.

### Paiement

Champs principaux :

- reservation ;
- payeur ;
- montant ;
- devise ;
- moyen de paiement ;
- fournisseur de paiement ;
- reference transaction ;
- statut : initie, reussi, echoue, rembourse ;
- date de paiement.

### Commission

Champs principaux :

- reservation ;
- type : pourcentage ou montant fixe ;
- taux ;
- montant ;
- statut ;
- regle appliquee.

### Abonnement

Champs principaux :

- proprietaire ;
- plan ;
- montant ;
- devise ;
- periodicite ;
- statut ;
- date debut ;
- date fin ;
- renouvellement automatique.

### Plan d'abonnement

Champs principaux :

- nom ;
- prix ;
- devise ;
- periodicite ;
- nombre d'espaces autorises ;
- niveau de visibilite ;
- commission reduite eventuelle ;
- fonctionnalites incluses ;
- statut.

### Reversement

Champs principaux :

- proprietaire ;
- reservation ;
- montant brut ;
- commission BAOBAA ;
- montant net ;
- statut ;
- date programmee ;
- date effectuee ;
- reference de paiement.

### Avis

Champs principaux :

- client ;
- espace ;
- reservation ;
- note ;
- commentaire ;
- statut de moderation ;
- date publication.

### Litige / Signalement

Champs principaux :

- reservation ;
- declarant ;
- type ;
- description ;
- pieces jointes ;
- statut ;
- decision ;
- montant rembourse eventuel.

### Notification

Champs principaux :

- utilisateur ;
- canal : email, sms, WhatsApp, in-app ;
- titre ;
- message ;
- statut ;
- date envoi.

### Parametre plateforme

Champs principaux :

- commission globale ;
- frais fixes ;
- devise par defaut ;
- pays actifs ;
- villes actives ;
- moyens de paiement actifs ;
- modele economique actif ;
- delai de reversement ;
- regles de validation.

## 7. Modeles economiques

BAOBAA doit pouvoir supporter trois modeles.

### Commission seule

Le proprietaire publie gratuitement. BAOBAA prend une commission sur chaque reservation payee.

Exemples :

- 10 % du montant de reservation ;
- 5 000 FCFA fixes par reservation ;
- 8 % + 1 000 FCFA de frais fixes.

### Abonnement seul

Le proprietaire paie pour publier ou maintenir ses espaces actifs.

Exemples :

- plan Basic : 1 espace actif ;
- plan Pro : 5 espaces actifs ;
- plan Premium : espaces illimites, meilleure visibilite, badge prioritaire.

### Commission + abonnement

Le proprietaire paie un abonnement et BAOBAA prend aussi une commission reduite.

Exemple :

- proprietaire sans abonnement : 12 % de commission ;
- proprietaire Pro : 5 % de commission + abonnement mensuel ;
- proprietaire Premium : 3 % de commission + mise en avant.

Le SAP doit pouvoir configurer ces regles sans intervention technique.

## 8. Parcours utilisateur

### Parcours client

1. Le client arrive sur l'accueil.
2. Il recherche par ville, type d'espace, date et nombre d'invites.
3. Il consulte les resultats.
4. Il filtre par budget, capacite, equipements et disponibilite.
5. Il ouvre une fiche espace.
6. Il consulte photos, prix, inclusions, regles, disponibilites et avis.
7. Il choisit une date et un creneau.
8. Il effectue une reservation directe ou envoie une demande.
9. Il paie le montant de reservation.
10. Il recoit une confirmation.
11. Apres l'evenement, il peut laisser un avis.

### Parcours proprietaire

1. Le proprietaire cree un compte.
2. Il complete son profil.
3. Il ajoute son espace.
4. Il ajoute photos, description, tarifs, disponibilites et regles.
5. Il soumet l'espace a validation.
6. BAOBAA verifie et publie.
7. Le proprietaire recoit des demandes ou reservations.
8. Il suit ses paiements et reversements.
9. Il gere son abonnement si applicable.

### Parcours SAP

1. Le SAP se connecte au back-office.
2. Il consulte les indicateurs globaux.
3. Il configure les commissions et abonnements.
4. Il supervise les validations et moderations.
5. Il suit les revenus, paiements et reversements.
6. Il gere les litiges majeurs.
7. Il ajuste les parametres de plateforme.

## 9. Pages principales

### Page d'accueil

Objectif : permettre de lancer une recherche immediatement.

Contenu :

- nom BAOBAA et baseline ;
- barre de recherche : type d'espace, ville, date, nombre d'invites ;
- categories populaires ;
- espaces populaires ;
- villes principales ;
- promesse de confiance ;
- appel a l'action pour publier son espace ;
- fonctionnement en trois etapes ;
- FAQ courte.

### Page listing des espaces

Objectif : comparer rapidement les espaces.

Elements :

- filtres persistants ;
- categories ;
- recherche texte ;
- ville et rayon ;
- date ;
- nombre d'invites ;
- budget ;
- equipements ;
- mode reservation instantanee ou sur demande ;
- cartes d'espaces ;
- carte geographique optionnelle ;
- tri par pertinence, prix, note, capacite, nouveaute.

Carte d'espace :

- photo principale ;
- nom ;
- ville/quartier ;
- type d'espace ;
- capacite ;
- prix a partir de ;
- badge verifie ;
- note ;
- disponibilite indicative ;
- bouton voir details.

### Fiche detail espace

Objectif : donner confiance et convertir en reservation.

Sections :

- galerie photos ;
- titre, ville, type, badges ;
- capacite, duree, surface ;
- prix et montant de reservation ;
- module de choix date/creneau ;
- bouton reserver ou demander ;
- hote/proprietaire ;
- description ;
- inclusions ;
- equipements ;
- capacite par configuration ;
- localisation approximative ;
- disponibilites ;
- regles de l'espace ;
- politique d'annulation ;
- avis ;
- FAQ ;
- espaces similaires.

### Tableau de bord proprietaire

Pages :

- accueil avec statistiques ;
- mes espaces ;
- ajouter un espace ;
- reservations ;
- calendrier ;
- tarifs ;
- paiements ;
- reversements ;
- abonnement ;
- profil ;
- support.

### Back-office SAP

Pages :

- tableau de bord global ;
- utilisateurs ;
- proprietaires ;
- espaces ;
- validations ;
- reservations ;
- paiements ;
- commissions ;
- abonnements ;
- reversements ;
- litiges ;
- avis ;
- villes et pays ;
- categories ;
- equipements ;
- contenus CMS ;
- parametres plateforme ;
- roles et permissions ;
- journal d'activite.

## 10. Regles metier importantes

### Publication d'un espace

- Un espace nouvellement cree doit etre en statut brouillon ou en attente de validation.
- Un espace ne devient visible publiquement qu'apres validation BAOBAA.
- BAOBAA peut suspendre un espace.
- Le proprietaire peut modifier son espace, mais certaines modifications sensibles peuvent repasser en validation.

### Disponibilite

- Une reservation confirmee bloque automatiquement le creneau.
- Un proprietaire peut bloquer manuellement des dates.
- Un client ne peut pas payer une reservation sur un creneau indisponible.
- Le systeme doit eviter les doubles reservations.

### Reservation

Deux modes doivent etre supportes :

- **Reservation instantanee :** le client paie directement si le creneau est disponible.
- **Reservation sur demande :** le proprietaire confirme avant paiement ou avant validation finale.

### Paiement

- Le montant de reservation est defini par le proprietaire.
- BAOBAA calcule automatiquement sa commission selon la regle active.
- Le paiement peut etre par Mobile Money, carte bancaire ou autre moyen local.
- Le statut de reservation depend du statut du paiement.

### Commission

- La commission peut etre globale, par plan, par pays, par categorie ou par proprietaire.
- La commission appliquee doit etre historisee.
- Une modification future de commission ne doit pas changer les anciennes reservations.

### Abonnement

- Un proprietaire peut avoir un plan actif, expire, suspendu ou annule.
- Le plan peut limiter le nombre d'espaces actifs.
- Le plan peut influencer la commission et la visibilite.

### Annulation

- Chaque espace doit avoir une politique d'annulation.
- Les remboursements suivent la politique active au moment de la reservation.
- Les annulations tardives peuvent entrainer des frais.

## 11. Design system et direction artistique

### Positionnement visuel

BAOBAA doit etre :

- moderne ;
- chaleureux ;
- fiable ;
- premium sans etre froid ;
- africain francophone sans tomber dans le decoratif excessif ;
- mobile-first.

### Couleurs recommandees

Palette de depart :

- vert baobab profond : confiance, nature, ancrage ;
- ivoire clair : fond doux et lisible ;
- or chaud : accent premium ;
- noir doux ou anthracite : texte principal ;
- rouge terre ou cuivre : accent secondaire.

La palette doit rester equilibree. Eviter une interface entierement verte ou entierement beige.

### Typographie

- titres forts, lisibles et modernes ;
- texte courant tres lisible ;
- chiffres et prix bien visibles ;
- hierarchie claire sur mobile.

### Composants UI

- barre de recherche centrale ;
- cartes d'espaces ;
- badges de verification ;
- filtres sous forme de boutons, selects, sliders et cases a cocher ;
- calendrier de disponibilite ;
- galerie photo ;
- tableau de bord dense mais lisible ;
- modales de confirmation ;
- alertes de statut ;
- tableaux admin avec filtres et actions rapides.

### Ton de contenu

Le ton doit etre :

- clair ;
- rassurant ;
- professionnel ;
- francophone ;
- proche du terrain.

Exemples de textes :

- "Trouvez l'espace ideal pour votre prochain evenement."
- "Comparez les salles verifiees autour de vous."
- "Reserve maintenant, finalise les details avec le proprietaire."
- "Publiez votre espace et recevez des demandes qualifiees."
- "Des lieux verifies pour des evenements sans mauvaise surprise."

## 12. Contenu public recommande

### Categories principales

- Salles de mariage
- Salles de conference
- Salles de spectacle
- Espaces plein air
- Rooftops et lounges
- Hotels et seminaires
- Jardins evenementiels
- Restaurants privatisables
- Espaces culturels
- Galeries et studios

### Villes de lancement possibles

- Abidjan
- Dakar
- Cotonou
- Lome
- Douala
- Yaounde
- Bamako
- Conakry
- Ouagadougou

### Promesses de confiance

- Espaces verifies
- Prix transparents
- Reservation securisee
- Paiement local
- Support BAOBAA
- Avis clients

## 13. Statuts principaux

### Statuts espace

- brouillon ;
- en attente de validation ;
- publie ;
- rejete ;
- suspendu ;
- archive.

### Statuts reservation

- brouillon ;
- en attente proprietaire ;
- en attente paiement ;
- confirmee ;
- refusee ;
- annulee ;
- terminee ;
- litige.

### Statuts paiement

- initie ;
- en attente ;
- reussi ;
- echoue ;
- rembourse ;
- partiellement rembourse.

### Statuts abonnement

- actif ;
- expire ;
- suspendu ;
- annule ;
- en attente paiement.

## 14. Notifications

Notifications client :

- reservation creee ;
- paiement reussi ;
- reservation confirmee ;
- reservation refusee ;
- rappel avant evenement ;
- demande d'avis.

Notifications proprietaire :

- nouvelle demande ;
- paiement recu ;
- reservation confirmee ;
- espace valide ;
- espace rejete ;
- abonnement bientot expire ;
- reversement effectue.

Notifications SAP/admin :

- nouvel espace a valider ;
- nouveau litige ;
- paiement important ;
- proprietaire a verifier ;
- echec de paiement ;
- demande de remboursement.

## 15. Indicateurs de tableau de bord

### SAP

- chiffre d'affaires total ;
- commissions gagnees ;
- abonnements actifs ;
- nombre de reservations ;
- taux de conversion ;
- espaces publies ;
- espaces en attente ;
- proprietaires actifs ;
- clients actifs ;
- litiges ouverts ;
- paiements echoues.

### Proprietaire

- revenus bruts ;
- revenus nets ;
- reservations confirmees ;
- demandes en attente ;
- taux d'occupation ;
- espaces les plus consultes ;
- avis moyens ;
- prochains evenements.

## 16. Exigences techniques initiales

Le projet etant une application Laravel, l'architecture doit rester simple et evolutive.

Recommandations :

- Laravel pour le backend ;
- Blade, Vue, React ou Inertia selon choix final du projet ;
- Eloquent pour les entites metier ;
- Policies et Gates pour les permissions ;
- Jobs pour notifications, paiements et reversements ;
- Events Laravel pour les changements de statut ;
- migrations propres et versionnees ;
- tests Pest pour les parcours critiques ;
- stockage media local en dev et cloud en production ;
- integration d'un fournisseur de paiement compatible Afrique francophone.

Parcours critiques a tester :

- creation d'espace par un proprietaire ;
- validation d'espace par admin ;
- recherche et filtrage ;
- verification de disponibilite ;
- creation de reservation ;
- paiement reussi ;
- calcul commission ;
- abonnement proprietaire ;
- annulation et remboursement ;
- interdiction de double reservation.

## 17. Priorites de developpement

### Phase 1 - Fondation

- authentification ;
- roles et permissions ;
- profils proprietaires ;
- gestion des espaces ;
- categories et equipements ;
- media ;
- back-office de validation.

### Phase 2 - Marketplace

- page accueil ;
- listing ;
- fiche detail ;
- recherche et filtres ;
- disponibilites ;
- reservation ;
- notifications de base.

### Phase 3 - Monétisation

- paiements ;
- commissions ;
- abonnements ;
- factures ;
- reversements ;
- tableau de bord financier SAP.

### Phase 4 - Confiance et croissance

- avis ;
- litiges ;
- badges ;
- mise en avant ;
- contenus SEO par ville et categorie ;
- assistant de recommandation.

## 18. Definition de succes du MVP

Le MVP est considere pret si :

- un proprietaire peut publier un espace complet ;
- BAOBAA peut valider cet espace ;
- un client peut rechercher, consulter et reserver ;
- un paiement peut etre initie et suivi ;
- la commission BAOBAA est calculee ;
- le SAP peut gerer les regles principales ;
- les statuts de reservation sont fiables ;
- les informations essentielles sont visibles sur mobile ;
- les donnees financieres sont traçables.

## 19. Notes de reutilisation

Ce document doit servir de base pour :

- creer les tickets de developpement ;
- definir les migrations et modeles ;
- concevoir les wireframes ;
- preparer le backlog MVP ;
- aligner les designers, developpeurs et decideurs ;
- documenter les choix produit au fil du projet.

Avant chaque nouvelle phase, completer ce cahier avec :

- les ecrans concernes ;
- les regles metier detaillees ;
- les champs exacts ;
- les permissions ;
- les cas d'erreur ;
- les tests attendus.
