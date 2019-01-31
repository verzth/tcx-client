# TCX Client compatibility TCX v3.0.0 - Authentication Module for Client-Server Transaction (One Way - Two Way).

### TCX is authentication module to secure API Server.

It's adapt OAuth2 scheme, but it use more simplify scheme which provide authentication for client-server without need to define scopes.

1. Authentication Type, TCX support three ways authentication type:
    - **One Way Transaction Code (OWTC)**: Client only need to use **app_id** and **app_public** to access Server APIs.
    - **Two Way Transaction Code (TWTC)**: Client has to use **app_id** and **app_public** to get access token, then it can be used to access APIs.
    - **Free Transaction Code (FTC)**: Client use master token to access APIs, without need to request token for every requests. You need to
      generate Master token manually.

    Client need to specify type by sending **X-TCX-TYPE** header in every APIs request.

2. How to generate credentials:
    - **app_pass** or **X-TCX-APP-PASS**, it can be generate by hashing plain of joined token (param, time, or none), application public key,
      and client dynamic token with SHA1. Client need to append the given dynamic token to hash result by splitting with **colon (:)**
      symbol, then encrypt it with base64.
      - Param base
      
          Sample Parameter:
          ```
          abc=123
          _xyz=789
          foo=bar
          def=456
          bar=ghi
          ```
          Expected Token:
          ```
          _xyz=789&abc=123&bar=ghi&def=456&foo=bar
          ```
      - Time base
        
        ```
        tcx_datetime=YYYYMMDDHHmmss
        ```
        
        Sample
        ```
        tcx_datetime=20181230235959 // For 23:59:59, 30 December 2018
        ```
        
      - None, just using application password and client dynamic token.
    - **token** or **X-TCX-TOKEN**, it's provided when Client authorizing to server, but you need to encrypt it with base64.

3. Authentication Headers.
    - Type **OWTC**:
        - **'X-TCX-TYPE'**: **'OWTC'**.
        - **'X-TCX-APP-ID'**: Client ID.
        - **'X-TCX-APP-PASS'**: Client Password.
    - Type **TWTC**:
        - **'X-TCX-TYPE'**: **'TWTC'**.
        - **'X-TCX-APP-ID'**: Client ID.
        - **'X-TCX-APP-PASS'**: Client Password.
        - **'X-TCX-TOKEN'**: Access Token, obtained by doing authorization.
    - Type **FTC**:
        - **'X-TCX-TYPE'**: **'FTC'**.
        - **'X-TCX-APP-ID'**: Client ID.
        - **'X-TCX-APP-PASS'**: Client Password.
        - **'X-TCX-TOKEN'**: Master Access Token.
        
4. Available Function.
    - **getID()** - Get Application ID.
    - **getSecret()** - Get Application Secret Key.
    - **getPublic()** - Get Application Public Key.
    - **getTokenType()** - Get Token authorization type mode.
    - **isDebug()** - Check debugging status.
    - **getTCXPass(&$params)** - Get Application Password.
    - **getTCXMasterKey($encrypt=false)** - Get Master Token key.
    - **getTCXAccess($pass, $datetime=false, $encrypt=false)** - Get Access Token key.
        
5. Response Status.

    | Status | Number | Code | Message | Note |
    | :----: | :----: | :---: | ------- | -------- |
    | 0 | 705000 | TCXREQ | TCX Authentication Required | Provide Authentication Header |
    | 0 | 205001 | TCXRJC | TCX Authentication Rejected | X-TCX-Type not supported or disabled |
    | 0 | 405002 | TCXAFX | TCX Authentication Failed | X-TCX-App-ID not found, invalid, or inactive |
    | 0 | 505003 | TCXPFX | TCX Pass did not match | X-TCX-App-Pass not passed, crosscheck point 2 |
    | 0 | 505004 | TCXMKF | TCX Master Key did not valid | Check the master access key (Only FTC) |
    | 0 | 505005 | TCXTFX | TCX Token did not valid | Check the access key (Only TWTC) |
    
    Sample Response:
    ```
    {
        "status": 0,
        "status_code": "TCXREQ",
        "status_number": "705000",
        "status_message": "TCX Authentication Required"
    }
    ```