# Token didattico HMAC e sessioni MySQL

## File modificati, aggiunti e rimossi

Modificati Infrastructure/Security/TokenManager.php, public/index.php, bootstrap.php, .gitignore e README.md. Creati Domain/Interfaces/Repositories/IAuthTokenRepository.php, Infrastructure/Repository/AuthTokenRepository.php, Infrastructure/Persistence/auth_token.sql e config/.env.example.

La configurazione privata è stata migrata in config/.env e verificata tramite il caricatore INI nativo, senza stampare valori. La chiave esistente è stata rinominata JWT_KEY, non generata né sostituita. Le vecchie impostazioni non utilizzate sono state eliminate. Rimossi config/config.example.php, il file PHP locale dopo la verifica della migrazione e la directory vuota usata dal precedente sistema. Nessun caricamento di configurazioni alternative rimane nel bootstrap.

Rimossi completamente accessRevocations e il meccanismo di revoca su filesystem. Nel workspace non erano presenti record di revoca da migrare. Modelli, Services, Controller, contratti pubblici dei token e query degli utenti/post non sono stati riscritti.

## Tabella e repository

Ispezionato MySQL locale esclusivamente in lettura. user.id è tinyint(3) unsigned, chiave primaria, e user usa InnoDB. auth_token era già presente: token_hash char(64) NOT NULL PRIMARY KEY, user_id tinyint(3) unsigned NOT NULL, expires_at bigint unsigned NOT NULL, foreign key verso user(id) con ON DELETE CASCADE, engine InnoDB.

Lo script SQL da applicare manualmente usa quindi TINYINT UNSIGNED per user_id, compatibile con la colonna reale. Non impone charset o collation. Non è stato eseguito e non modifica user/post. IF NOT EXISTS evita di ricreare una tabella esistente; non sostituisce la verifica dello schema in altri ambienti.

AuthTokenRepository riceve DbConnector e implementa soltanto registrazione, verifica e cancellazione. Usa PDO/prepared statements. La verifica richiede corrispondenza di hash, utente, scadenza e timestamp futuro. La registrazione intercetta soltanto il duplicato MySQL 1062 e lo accetta solo se esiste già il medesimo record valido; gli altri errori vengono propagati. Nessun INSERT IGNORE o eccezione SQL ignorata indiscriminatamente.

## Token

generate conserva la struttura originale: payload con soli id ed exp, Base64, HMAC-SHA512 con la chiave ricevuta e restituzione di signature.payload. exp è calcolato una sola volta come time() + 3600, e riutilizzato nella persistenza. Prima della restituzione viene registrato SHA-256 del token completo, non il token in chiaro. Un errore di registrazione impedisce di restituire il token.

validate e getId condividono i controlli sul formato, firma, payload con esattamente due campi interi, identità positiva e scadenza futura. Soltanto un record MySQL corrispondente rende il token valido. Gli errori tecnici del database risalgono al gestore HTTP come 500, senza autorizzare la richiesta o esporre dettagli.

invalidate controlla la validità e cancella la riga identificata dall'hash, restituendo l'esito. Non riscrive payload o scadenza. Logout continua a usare il Bearer header. I record scaduti sono invalidi anche se ancora presenti; la query facoltativa di pulizia manuale è nel README.

Il token è proprietario, non JWT. Due login dello stesso utente nello stesso secondo possono condividere token e revoca. Un nuovo login nello stesso secondo dopo un logout può registrare nuovamente lo stesso token. Non sono stati aggiunti identificatori per nascondere questo limite. I precedenti token con campi aggiuntivi e quelli assenti dalla tabella richiedono un nuovo login.

## Configurazione, DI e architettura

Il solo meccanismo di configurazione è config/.env letto da parse_ini_file con INI_SCANNER_RAW nel bootstrap e trasferito in $_ENV, rispettando l'ambiente del processo. JWT_KEY deve essere generata manualmente in modo sicuro; il costruttore controlla almeno 32 byte e rifiuta valori interamente vuoti/spazi. Non esiste un fallback utilizzabile per una chiave mancante. Nessuna durata configurabile: sempre un'ora. Le regole INI per assegnazioni, commenti e valori speciali sono nel README.

Ogni closure del Composition Root costruisce AuthTokenRepository con la stessa istanza Singleton di DbConnector usata dagli altri Repository, poi inietta IAuthTokenRepository e JWT_KEY in TokenManager. AuthManager e AuthMiddleware continuano a dipendere da ITokenManager. Cinque livelli, DI manuale, ruoli, route e risposte pubbliche restano invariati. Nessun container, nuova libreria, ORM, test o parser personalizzato.

**Limitazione Git da completare manualmente:** config/.env era tracciato nel repository originale. Le regole .gitignore sono corrette, ma il comando di rimozione dall'indice è fallito perché l'ambiente vieta la scrittura di .git/index.lock. Prima di includere modifiche in un commit occorre eseguire `git rm --cached -- config/.env`, che conserva il file locale. Fino ad allora il solo .gitignore non protegge il file già tracciato. Ruotare le credenziali/segreti precedentemente versionati se ancora utilizzati. Nessun valore è stato stampato e la cronologia non è stata riscritta.

## Verifiche e limiti

Verifiche limitate a sintassi, autoload nativo, interfacce, costruttori, metadati SQL e rimozione dei riferimenti al sistema precedente. Nessun token generato o registrato durante le verifiche, nessuna modifica ai dati e nessuna applicazione automatica dello script SQL. Nessun test automatizzato, fixture o mock creato. Nessun commit/push.

- Sintassi PHP: 30 file verificati, zero errori.
- Autoload nativo: 28 classi/interfacce caricate; le dichiarazioni dei Repository implementano coerentemente le interfacce.
- Configurazione: JWT_KEY caricata e presente con lunghezza sufficiente, senza esporla; migrazione verificata confrontando tutti i valori prima di eliminare il file precedente.
- DI: costruiti AuthTokenRepository e TokenManager con le dipendenze reali; nessun metodo di generazione, registrazione o eliminazione invocato. Confermata la stessa istanza DbConnector tra chiamate a getInstance.
- Schema: letti metadati di user.id, colonne auth_token, engine e foreign key; compatibilità confermata. Nessun valore dei record letto o stampato.
- Ricerca statica: nessun campo aggiuntivo del vecchio payload, vecchio nome della chiave/durata/storage, metodo o funzione di revoca filesystem nel codice eseguibile. Nessuna dipendenza esterna o caricatore di librerie.
- Assenti cartelle delle librerie esterne, manifest, vecchi file PHP di configurazione, directory delle revoche e tests.
- Regola di esclusione di config/.env confermata ignorando lo stato dell'indice; il file resta tracciato fino al comando manuale indicato sopra, a causa del limite di scrittura dell'ambiente.
- git diff --check, escludendo il file privato: superato. Il contenuto del file privato non è stato incluso nei report o nei diff stampati.

Non sono stati eseguiti login/logout completi o scritture reali per provare la registrazione duplicata. La compatibilità SQL è stata verificata sui metadati dello schema esistente, non applicando DDL o dati di prova. Il risultato delle verifiche delle iterazioni precedenti non viene presentato come verifica funzionale di questa implementazione.
