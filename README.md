# User Management API

Questa è una semplice API REST in PHP per la gestione degli utenti, che utilizza un file `data.json` come database e supporta risposte in formato JSON e XML.

## Base URL

`http://localhost/wsphp/api/user`

## Endpoint

### 1. Ottieni tutti gli utenti

Restituisce la lista completa degli utenti salvati nel file `data.json`.

* **URL:** `/`
* **Metodo:** `GET`
* **Header Consigliati:** `Accept: application/json` oppure `Accept: application/xml`
* **Risposta Successo (200 OK):**

```json
{
  "0": { "nome": "Mario", "email": "mario@email.com" },
  "1": { "nome": "Luigi", "email": "luigi@email.com" }
}

```



### 2. Ottieni singolo utente

Restituisce i dettagli di un utente specifico tramite il suo ID.

* **URL:** `/{id}`
* **Metodo:** `GET`
* **Esempio:** `/0`
* **Risposta Errore (404 Not Found):** `{"errore": "Utente non trovato"}`

### 3. Crea nuovo utente

Aggiunge un nuovo utente al sistema. L'ID viene generato automaticamente.

* **URL:** `/`
* **Metodo:** `POST`
* **Header:** `Content-Type: application/json`
* **Body (JSON):**

```json
{
  "nome": "Luca Rossi",
  "email": "luca@esempio.it"
}

```


* **Validazione:** Sono ammessi solo i campi `nome` ed `email`. Altri campi causeranno un errore `400`.



### 4. Aggiorna utente

Modifica i dati di un utente esistente.

* **URL:** `/{id}`
* **Metodo:** `PUT`
* **Body (JSON):**
```json
{
  "nome": "Mario Rossi",
  "email": "mario.nuova@email.com"
}

```



### 5. Elimina utente

Rimuove definitivamente un utente dal database JSON.

* **URL:** `/{id}`
* **Metodo:** `DELETE`
* **Risposta Successo (200 OK):** `{"messaggio": "Utente eliminato"}`
---
