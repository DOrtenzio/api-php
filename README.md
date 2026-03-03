# User Management API

Questa è una semplice API REST in PHP per la gestione degli utenti. Utilizza un file `data.json` come database flat-file e supporta richieste/risposte in formato **JSON** e **XML**.

## Base URL

L'entry point dell'API è configurato per rispondere al seguente percorso:

```
http://localhost/wsphp/api
```

---

## Endpoint

### 1. Ottieni tutti gli utenti

Restituisce la lista completa degli utenti.

* **URL:** `/`
* **Metodo:** `GET`
* **Header:** `Accept: application/json` oppure `Accept: application/xml`
* **Risposta Successo (200 OK):**

```json
[
  { "id": 0, "name": "Mario Rossi", "age": "30", "date": "2023-10-01" },
  { "id": 1, "name": "Luigi Verdi", "age": "25", "date": "2023-10-05" }
]
```

---

### 2. Ottieni singolo utente

* **URL:** `/{id}`
* **Metodo:** `GET`
* **Esempio:** `/api/0`
* **Risposta Errore (404 Not Found):**

```json
{"errore": "Id inesistente"}
```

---

### 3. Crea nuovo utente

L'ID viene generato automaticamente incrementando il valore massimo esistente.

* **URL:** `/`
* **Metodo:** `POST`
* **Header:** `Content-Type: application/json` o `application/xml`
* **Body (JSON):**

```json
{
  "name": "Luca Bianchi",
  "age": "22",
  "date": "2023-11-12"
}
```

* **Nota:** I campi `name`, `age` e `date` sono **obbligatori**.

---

### 4. Aggiorna utente

Sostituisce i dati di un utente esistente.

* **URL:** `/{id}`
* **Metodo:** `PUT`
* **Body (JSON):**

```json
{
  "name": "Luca Bianchi Modificato",
  "age": "23",
  "date": "2023-11-12"
}
```

* **Risposta Errore (404 Not Found):** `{"errore": "ID mancante o utente inesistente"}`

---

### 5. Elimina utente

Rimuove l'utente dal file `data.json`.

* **URL:** `/{id}`
* **Metodo:** `DELETE`
* **Risposta Successo (200 OK):**

```json
{"messaggio": "Utente eliminato"}
```

* **Risposta Errore (404 Not Found):**

```json
{"errore": "Impossibile eliminare: ID non trovato"}
```

---

Se vuoi, posso anche aggiungere una **sezione con esempi di richieste cURL e XML**, così la documentazione diventa completa e pronta per essere condivisa con altri sviluppatori.

Vuoi che faccia anche quella parte?
