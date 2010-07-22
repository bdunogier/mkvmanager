==============
MKV Auto-merge
==============

Idée de base
============
Le merge est effectué par un crontab/daemon qui dépile une queue de conversions.
Après le merge, le symlink vers aggregateshare est automatiquement fait, et le
fichier original est supprimé après vérification.
L'ajout d'éléments à la queue est effectué par un autre script.

Composantes
===========

DB
--
BDD SQLite mergequeue.db[#] avec table .

SQL Create statement[#][#]::
	CREATE TABLE commands (time INTEGER PRIMARY KEY, command TEXT, pid INTEGER);

.. [#] TODO: déplacer dans un autre dossier
.. [#] TODO: ajouter champ status. 0 = todo, 1 = done, 2 = erreur
.. [#] TODO: ajouter champ message. Contient l'erreur si status = 1, ou la sortie de la commande en cas de succès

Cron/Daemon
-----------

Au démarrage, compte les conversions en attente (status = 0) dans mergequeue.
Si conversion(s) trouvée(s), les prend une par une, et effectue la conversion.

Doit être exécuté en tant que root:
* demande les droits media pour stocker et linker
* demande les droits download pour supprimer l'original

Alternative: utilisateur conjoint download/media !

Script de queue
---------------

Convertisseur de commande windows + smb => linux / fs local.
Détecte le type de video (TV Show / Movie) d'après le chemin.
Script actuel: tools/mkvmerge.php
Peut déjà stocker une commande en queue, reste à traiter le sudo (supprimer)