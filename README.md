# SocialApi

API social in PHP puro, organizzata in cinque livelli: API, Application, Business, Domain e Infrastructure. Sintassi e componenti esistenti seguono il commit originale `8d21efa30c2f9781e53de211b6f4738791f383f1`; la separazione delle responsabilità prende come riferimento [ElectronicRegister](https://github.com/xxdema10xx/ElectronicRegister/tree/refactor-database-entities/Backend/ElectronicRegisterAPI/src).

## Struttura

```text
Api/
    Controllers/
        AuthController.php
        UserController.php
        PostController.php
    middleware/
        AuthMiddleware.php
    Request.php
    JsonResponse.php
    Router.php
Application/
    Managers/
        AuthManager.php
        UserManager.php
        PostManager.php
Business/
    Services/
        AuthService.php
        UserService.php
        PostService.php
Domain/
    Interfaces/Repositories/
        IUserRepository.php
        IPostRepository.php
        IAuthTokenRepository.php
    Interfaces/Security/
        ITokenManager.php
    Interfaces/Serializers/
        IUserSerializer.php
        IPostSerializer.php
    Models/
        User.php
        Post.php
Infrastructure/
    Persistence/
        DbConnector.php
        auth_token.sql
    Repository/
        UserRepository.php
        PostRepository.php
        AuthTokenRepository.php
    Serializers/
        UserSerializer.php
        PostSerializer.php
    Security/
        TokenManager.php
config/
    .env.example
    .env (privato)
public/
    index.php
    .htaccess
bootstrap.php
```

Il flusso è **Controller → Manager → Service**. I Controller leggono parametri e contesto autenticato, chiamano il rispettivo Manager e producono risposte tramite i Serializers. I Managers coordinano i casi d'uso, verificano l'esistenza delle risorse e usano i Repository. I Services applicano le regole su credenziali, utenti, contenuto, ruoli e paginazione, senza database, HTTP o dettagli di Infrastructure.

I Repository contengono SQL e prepared statements e implementano le interfacce del Domain, inclusa IAuthTokenRepository per le sessioni. I Serializers convertono record e modelli ed espongono soltanto i campi pubblici. I Models e i contratti del Domain non dipendono da HTTP o PDO. Le eccezioni PHP standard rappresentano gli errori; soltanto JsonResponse le traduce in status HTTP.

Le dipendenze vengono ricevute tramite **constructor injection manuale**, con proprietà private promosse. I Managers dipendono dalle interfacce dei Repository e, per i token, da ITokenManager. Controller e Repository ricevono anche i contratti dei Serializers necessari. Non vengono creati Manager, Service, Repository o componenti di sicurezza dentro i metodi applicativi.

**public/index.php è il Composition Root**: tre closure esplicite costruiscono soltanto il grafo del Controller richiesto, quando viene eseguita la route. Condividono le dipendenze necessarie al middleware e al Controller nella stessa operazione. Il Router conserva gli handler registrati e si limita al dispatch; non costruisce dipendenze. Una richiesta a login non costruisce il grafo dei post. Le risposte 404, 405 e OPTIONS non inizializzano i grafi applicativi.

DbConnector è l'unico Singleton: il Composition Root chiama getInstance e passa la stessa connessione ai Repository della richiesta. Gli altri componenti sono oggetti ordinari. bootstrap.php registra un semplice spl_autoload_register per il namespace SocialApi, carica la configurazione e imposta la timezone. Nessuna classe autoloader, libreria esterna, container, autowiring, service locator o framework. Non sono presenti infrastrutture di testing.

## Configurazione

Requisiti: PHP 8.1+ con le estensioni native PDO, pdo_mysql e mbstring, e MySQL/MariaDB. Non è necessaria alcuna installazione di pacchetti PHP.

```powershell
Copy-Item config/.env.example config/.env
```

Non sovrascrivere un file .env già presente. Impostare DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD e **JWT_KEY**. La configurazione di questo workspace è già stata migrata, preservando credenziali e chiave esistenti senza stamparle. Non viene generato alcun segreto automaticamente.

JWT_KEY deve essere generata manualmente da una fonte crittograficamente sicura: almeno 32 byte casuali, ad esempio rappresentati da 64 caratteri esadecimali. Il codice richiede una chiave di almeno 32 byte, non vuota o composta soltanto da spazi; non può verificare come sia stata generata. Nessun valore di ripiego viene accettato. Il nome della variabile non cambia il formato proprietario del token.

public/index.php carica il bootstrap con require_once. Il bootstrap usa soltanto parse_ini_file con INI_SCANNER_RAW, trasferisce le impostazioni in $_ENV e rispetta le variabili del processo già definite. Non esiste un secondo meccanismo di configurazione. Usare semplici righe CHIAVE=VALORE; racchiudere tra doppi apici valori con spazi, punti e virgola, # o =. I commenti iniziano con ;. Non usare export, sezioni, espansioni di variabili o valori multilinea. INI_SCANNER_RAW mantiene i valori come stringhe.

La configurazione resta fuori da public. Non vengono esposti stack trace, neppure con APP_DEBUG. **Il file .env era tracciato nel repository originale**: .gitignore lo esclude, ma questo ambiente non consente di modificare l'indice Git. Prima di aggiungere modifiche a un commit, eseguire manualmente:

```powershell
git rm --cached -- config/.env
```

Il comando conserva il file locale e ne interrompe il tracciamento. La nuova configurazione non deve essere inclusa nei commit.

Schema esistente senza modifiche: `user(id, username, password, role)` e `post(id, user_id, content, creation_date, delete_date)`. Username unico di massimo 25 caratteri; contenuto di massimo 300 caratteri. Password bcrypt, massimo 72 byte e senza byte NUL.

Per le sessioni applicare manualmente [Infrastructure/Persistence/auth_token.sql](Infrastructure/Persistence/auth_token.sql) al database configurato, se la tabella non esiste. Lo script non modifica user o post. Nel database locale la tabella auth_token è già presente e compatibile: token_hash CHAR(64) come chiave primaria, user_id TINYINT UNSIGNED come user.id, expires_at BIGINT UNSIGNED, foreign key verso user(id) con ON DELETE CASCADE. Il tipo TINYINT UNSIGNED è intenzionale: INT non sarebbe compatibile con la foreign key dello schema attuale. Su altri database verificare prima che il tipo di user_id corrisponda a user.id. CREATE TABLE IF NOT EXISTS non corregge una tabella esistente incompatibile.

## Avvio

Dalla radice del progetto:

```powershell
php -S 127.0.0.1:8080 -t public public/index.php
```

Su Apache, MAMP o XAMPP abilitare mod_rewrite e configurare il DocumentRoot sulla cartella **public**, con AllowOverride All:

```apache
<VirtualHost *:80>
    ServerName socialapi.local
    DocumentRoot "C:/MAMP/htdocs/SocialApi/public"
    <Directory "C:/MAMP/htdocs/SocialApi/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Adattare il percorso per XAMPP, aggiungere `127.0.0.1 socialapi.local` al file hosts e riavviare Apache. `public/.htaccess` inoltra le richieste all'unico ingresso `public/index.php` e conserva l'header Authorization. La radice del repository non deve essere esposta via HTTP.

## Route

| Metodo | Route | Body JSON | Risposta |
|---|---|---|---|
| POST | /auth/login | username, password | 200, token |
| POST | /auth/register | username, password, password-check | 201, success |
| POST | /auth/logout | Nessuno | 200, logout |
| GET | /users/me | Nessuno | 200, user |
| GET | /posts?page=1 | Nessuno | 200, pagination e posts |
| POST | /posts | content | 201, success |
| PUT | /posts/{id} | content | 200, success |
| DELETE | /posts/{id} | Nessuno | 200, success |

Tutte le route salvo login e registrazione richiedono `Authorization: Bearer <token>`. I ruoli sono `user`, `mod`, `admin`. Tutti possono leggere e creare post; **soltanto mod/admin possono modificarli o eliminarli, anche quando sono autori diversi**. Un utente normale non può modificare/eliminare neppure i propri post. La registrazione assegna sempre user.

Il middleware rilegge utente e ruolo dal database per ogni richiesta protetta. Password e hash non vengono serializzati. La cancellazione valorizza delete_date senza eliminare il record; il post non compare più nelle letture e nelle successive modifiche. Il campo pubblico delete_date resta booleano, come nell'originale.

Paginazione di 10 post, page intero positivo e default 1. Ordinamento per creazione e ID decrescenti. Pagine vuote restituiscono un array vuoto. La risposta conserva `pagination.total_posts`, `total_pages`, `posts_per_page`, `current_page`.

Gli errori hanno formato `{"error":"codice"}`. Status: 400 input/JSON invalido, 401 autenticazione, 403 autorizzazione, 404 risorsa assente, 405 metodo non consentito con Allow, 409 username occupato, 500 errore interno sanitizzato. OPTIONS sulle route note restituisce 204. Non è stata aggiunta una lettura HTTP del singolo post.

## Token e sicurezza

Il token è un formato **HMAC proprietario, non JWT**: signature.payload, con firma HMAC-SHA512 esadecimale e payload Base64 contenente soltanto id ed exp. La scadenza è sempre time() + 3600, cioè un'ora. Il client lo tratta come stringa opaca. A exp <= time() il token non è valido.

TokenManager riceve IAuthTokenRepository e JWT_KEY dal Composition Root. Mantiene generate, validate, invalidate, getId, encode e decode. generate firma il payload con hash_hmac('sha512', $payload, $this->secret), registra soltanto SHA-256 del token completo insieme all'utente e alla stessa scadenza e restituisce il token originale. Se la scrittura fallisce, il token non viene restituito.

validate verifica firma con hash_equals, Base64, esattamente due campi interi id/exp e scadenza. Poi richiede un record con lo stesso hash, lo stesso utente e la stessa scadenza ancora futura. Una firma corretta senza record non basta. Errori SQL o di connessione non autorizzano il token: risalgono alla gestione centralizzata e producono 500 senza dettagli sensibili. getId applica tutti gli stessi controlli prima di restituire l'identità.

Logout legge il Bearer header, verifica il token ed elimina il record: tutte le successive verifiche lo rifiutano. MySQL è l'unica fonte di verità delle sessioni. I record scaduti non sono accettati anche se non ancora eliminati. Per la pulizia manuale, quando necessaria:

```sql
DELETE FROM auth_token WHERE expires_at <= UNIX_TIMESTAMP();
```

Due login dello stesso utente nello stesso secondo possono produrre lo stesso token: la registrazione è idempotente soltanto per il duplicato MySQL 1062 con dati corrispondenti. Altri errori non vengono ignorati. Le due sessioni condividono quindi la revoca; un nuovo login nello stesso secondo dopo il logout può registrare nuovamente il medesimo token. Questo limite deriva dal formato deterministico richiesto, senza campi aggiuntivi.

I token della precedente implementazione con campi aggiuntivi vengono rifiutati. Anche i token nel formato a due campi sono rifiutati se assenti dalla tabella. Dopo la migrazione occorre effettuare nuovamente il login. I vecchi URL PHP restano eliminati; route, payload pubblici e regole sui ruoli non cambiano. Eventuali account storici con doppio hashing richiedono un cambio password amministrativo.

Ruotare le credenziali e i segreti già pubblicati nella cronologia originale, se ancora utilizzati. La chiave locale esistente è stata rinominata JWT_KEY, non rigenerata. Nessuna riscrittura della cronologia Git.

## Controlli tecnici

```powershell
Get-ChildItem Api,Application,Business,Domain,Infrastructure,public -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
php -l bootstrap.php
```

Non sono presenti test, fixture, mock, configurazioni PHPUnit o script di testing. Le verifiche di questa revisione sono limitate a sintassi, caricamento delle classi/interfacce, coerenza della DI e lettura dei metadati MySQL. Nessuno script SQL o flusso di scrittura è stato eseguito automaticamente. Dettagli in [REFACTORING_REPORT.md](REFACTORING_REPORT.md).
