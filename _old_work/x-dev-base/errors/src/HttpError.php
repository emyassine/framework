<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Errors;

final class HttpError
{
    public static function reason(int $code): string
    {
        return self::phrases()[$code] ?? 'Response';
    }

    public static function status_label_width(): int
    {
        $widths = \array_map(
            static fn (int $code, string $reason): int => \strlen($code.' '.$reason),
            \array_keys(self::phrases()),
            self::phrases(),
        );

        return \max($widths);
    }

    public static function get(int $code, ?string $locale = null): ErrorDefinition
    {
        $phrase = self::reason($code);
        $row = self::errors()[$code] ?? self::default_error();

        return new ErrorDefinition(
            code: $code,
            phrase: $phrase,
            icon: $row['icon'],
            title: fast_i18n($row['title'], $locale, 'errors_'.$code.'_title'),
            description: fast_i18n($row['description'], $locale, 'errors_'.$code.'_description'),
            accent: $row['accent'],
        );
    }

    /**
     * @return array<int, string>
     */
    public static function phrases(): array
    {
        return [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            103 => 'Early Hints',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a Teapot',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Too Early',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];
    }

    /**
     * @return array<int, array{icon: string, title: array<string, string>, description: array<string, string>, accent: string}>
     */
    public static function errors(): array
    {
        return [
            400 => self::row('heroicon-o-exclamation-circle', ['en' => 'Bad Request', 'fr' => 'Requête invalide', 'es' => 'Solicitud incorrecta', 'ar' => 'طلب غير صالح'], ['en' => 'The request could not be understood by the server. Please check your input and try again.', 'fr' => 'Le serveur n\'a pas pu comprendre la requête. Veuillez vérifier votre saisie et réessayer.', 'es' => 'El servidor no pudo entender la solicitud. Revisa tu entrada e inténtalo de nuevo.', 'ar' => 'تعذّر على الخادم فهم الطلب. يرجى التحقق من المدخلات والمحاولة مجدداً.'], 'warning'),
            401 => self::row('heroicon-o-lock-closed', ['en' => 'Unauthorized', 'fr' => 'Non autorisé', 'es' => 'No autorizado', 'ar' => 'غير مصرح'], ['en' => 'You need to be authenticated to access this resource.', 'fr' => 'Vous devez être authentifié pour accéder à cette ressource.', 'es' => 'Debes estar autenticado para acceder a este recurso.', 'ar' => 'يجب أن تكون مسجّلاً للدخول للوصول إلى هذا المورد.'], 'warning'),
            402 => self::row('heroicon-o-credit-card', ['en' => 'Payment Required', 'fr' => 'Paiement requis', 'es' => 'Pago requerido', 'ar' => 'الدفع مطلوب'], ['en' => 'Access to this resource requires a valid payment.', 'fr' => 'L\'accès à cette ressource nécessite un paiement valide.', 'es' => 'El acceso a este recurso requiere un pago válido.', 'ar' => 'يتطلب الوصول إلى هذا المورد دفعاً صالحاً.'], 'warning'),
            403 => self::row('heroicon-o-no-symbol', ['en' => 'Access Forbidden', 'fr' => 'Accès interdit', 'es' => 'Acceso prohibido', 'ar' => 'الوصول محظور'], ['en' => 'You do not have permission to access this page.', 'fr' => 'Vous n\'avez pas la permission d\'accéder à cette page.', 'es' => 'No tienes permiso para acceder a esta página.', 'ar' => 'ليس لديك صلاحية للوصول إلى هذه الصفحة.'], 'warning'),
            404 => self::row('heroicon-o-globe-alt', ['en' => 'Page Not Found', 'fr' => 'Page introuvable', 'es' => 'Página no encontrada', 'ar' => 'الصفحة غير موجودة'], ['en' => 'We cannot find the page you are looking for.', 'fr' => 'Nous ne trouvons pas la page que vous recherchez.', 'es' => 'No encontramos la página que buscas.', 'ar' => 'لا يمكننا العثور على الصفحة التي تبحث عنها.'], 'gray'),
            405 => self::row('heroicon-o-minus-circle', ['en' => 'Method Not Allowed', 'fr' => 'Méthode non autorisée', 'es' => 'Método no permitido', 'ar' => 'الطريقة غير مسموح بها'], ['en' => 'The HTTP method used is not supported for this endpoint.', 'fr' => 'La méthode HTTP utilisée n\'est pas prise en charge pour ce point de terminaison.', 'es' => 'El método HTTP utilizado no está soportado en este endpoint.', 'ar' => 'طريقة HTTP المستخدمة غير مدعومة لهذه النقطة.'], 'warning'),
            406 => self::row('heroicon-o-adjustments-horizontal', ['en' => 'Not Acceptable', 'fr' => 'Non acceptable', 'es' => 'No aceptable', 'ar' => 'غير مقبول'], ['en' => 'The server cannot produce a response matching the accepted values.', 'fr' => 'Le serveur ne peut pas produire une réponse correspondant aux valeurs acceptées.', 'es' => 'El servidor no puede generar una respuesta que coincida con los valores aceptables.', 'ar' => 'لا يستطيع الخادم إنتاج استجابة تتطابق مع القيم المقبولة.'], 'warning'),
            407 => self::row('heroicon-o-shield-check', ['en' => 'Proxy Authentication Required', 'fr' => 'Authentification proxy requise', 'es' => 'Autenticación de proxy requerida', 'ar' => 'مصادقة الوكيل مطلوبة'], ['en' => 'You must authenticate with a proxy server before this request can be served.', 'fr' => 'Vous devez vous authentifier auprès d\'un serveur proxy avant que cette requête puisse être traitée.', 'es' => 'Debes autenticarte con un servidor proxy antes de procesar esta solicitud.', 'ar' => 'يجب عليك المصادقة مع خادم وكيل قبل معالجة هذا الطلب.'], 'warning'),
            408 => self::row('heroicon-o-clock', ['en' => 'Request Timeout', 'fr' => 'Délai de requête dépassé', 'es' => 'Tiempo de solicitud agotado', 'ar' => 'انتهت مهلة الطلب'], ['en' => 'The server timed out waiting for the request.', 'fr' => 'Le serveur a expiré en attendant la requête.', 'es' => 'El servidor agotó el tiempo de espera para la solicitud.', 'ar' => 'انتهت مهلة الخادم أثناء انتظار الطلب.'], 'warning'),
            409 => self::row('heroicon-o-arrows-right-left', ['en' => 'Conflict', 'fr' => 'Conflit', 'es' => 'Conflicto', 'ar' => 'تعارض'], ['en' => 'The request conflicts with the current state of the resource.', 'fr' => 'La requête entre en conflit avec l\'état actuel de la ressource.', 'es' => 'La solicitud entra en conflicto con el estado actual del recurso.', 'ar' => 'يتعارض الطلب مع الحالة الحالية للمورد.'], 'warning'),
            410 => self::row('heroicon-o-trash', ['en' => 'Gone', 'fr' => 'Disparu', 'es' => 'Eliminado', 'ar' => 'غير متاح نهائياً'], ['en' => 'The requested resource has been permanently removed.', 'fr' => 'La ressource demandée a été définitivement supprimée.', 'es' => 'El recurso solicitado ha sido eliminado permanentemente.', 'ar' => 'تمت إزالة المورد المطلوب نهائياً.'], 'gray'),
            411 => self::row('heroicon-o-arrows-up-down', ['en' => 'Length Required', 'fr' => 'Longueur requise', 'es' => 'Longitud requerida', 'ar' => 'الطول مطلوب'], ['en' => 'The server requires a Content-Length header for this request.', 'fr' => 'Le serveur requiert un en-tête Content-Length pour cette requête.', 'es' => 'El servidor requiere una cabecera Content-Length para esta solicitud.', 'ar' => 'يتطلب الخادم رأس Content-Length لهذا الطلب.'], 'warning'),
            412 => self::row('heroicon-o-clipboard-document-check', ['en' => 'Precondition Failed', 'fr' => 'Précondition échouée', 'es' => 'Condición previa fallida', 'ar' => 'فشل الشرط المسبق'], ['en' => 'One or more request preconditions were not met.', 'fr' => 'Une ou plusieurs préconditions de la requête n\'ont pas été satisfaites.', 'es' => 'No se cumplieron una o más condiciones previas de la solicitud.', 'ar' => 'لم يتم استيفاء شرط واحد أو أكثر في الطلب.'], 'warning'),
            413 => self::row('heroicon-o-arrow-up-tray', ['en' => 'Payload Too Large', 'fr' => 'Charge utile trop volumineuse', 'es' => 'Carga útil demasiado grande', 'ar' => 'حجم البيانات كبير جداً'], ['en' => 'The request payload exceeds the maximum size allowed by the server.', 'fr' => 'La charge utile dépasse la taille maximale autorisée par le serveur.', 'es' => 'La carga útil supera el tamaño máximo permitido por el servidor.', 'ar' => 'حجم بيانات الطلب يتجاوز الحد الأقصى المسموح به.'], 'warning'),
            414 => self::row('heroicon-o-link', ['en' => 'URI Too Long', 'fr' => 'URI trop longue', 'es' => 'URI demasiado larga', 'ar' => 'عنوان URL طويل جداً'], ['en' => 'The provided URI was too long for the server to process.', 'fr' => 'L\'URI fournie était trop longue pour être traitée par le serveur.', 'es' => 'La URI proporcionada era demasiado larga para que el servidor la procesara.', 'ar' => 'عنوان URL المقدَّم طويل جداً بحيث لا يستطيع الخادم معالجته.'], 'warning'),
            415 => self::row('heroicon-o-document', ['en' => 'Unsupported Media Type', 'fr' => 'Type de média non pris en charge', 'es' => 'Tipo de medio no compatible', 'ar' => 'نوع الوسائط غير مدعوم'], ['en' => 'The media format of the request is not supported by the server.', 'fr' => 'Le format multimédia de la requête n\'est pas pris en charge par le serveur.', 'es' => 'El formato multimedia de la solicitud no es compatible con el servidor.', 'ar' => 'تنسيق الوسائط في الطلب غير مدعوم من قِبل الخادم.'], 'warning'),
            416 => self::row('heroicon-o-scissors', ['en' => 'Range Not Satisfiable', 'fr' => 'Plage non satisfaisable', 'es' => 'Rango no satisfactible', 'ar' => 'النطاق غير قابل للتنفيذ'], ['en' => 'The requested range cannot be fulfilled by the server.', 'fr' => 'La plage demandée ne peut pas être satisfaite par le serveur.', 'es' => 'El rango solicitado no puede ser atendido por el servidor.', 'ar' => 'لا يمكن للخادم تلبية النطاق المطلوب.'], 'warning'),
            417 => self::row('heroicon-o-chat-bubble-bottom-center-text', ['en' => 'Expectation Failed', 'fr' => 'Attente échouée', 'es' => 'Expectativa fallida', 'ar' => 'فشل التوقع'], ['en' => 'The server cannot meet the request expectations.', 'fr' => 'Le serveur ne peut pas satisfaire les attentes de la requête.', 'es' => 'El servidor no puede cumplir las expectativas de la solicitud.', 'ar' => 'لا يستطيع الخادم تلبية توقعات الطلب.'], 'warning'),
            418 => self::row('heroicon-o-face-smile', ['en' => 'I\'m a Teapot', 'fr' => 'Je suis une théière', 'es' => 'Soy una tetera', 'ar' => 'أنا إبريق شاي'], ['en' => 'The server refuses to brew coffee because it is a teapot.', 'fr' => 'Le serveur refuse de préparer du café car il est une théière.', 'es' => 'El servidor se niega a preparar café porque es una tetera.', 'ar' => 'يرفض الخادم تحضير القهوة لأنه إبريق شاي.'], 'gray'),
            421 => self::row('heroicon-o-arrow-path-rounded-square', ['en' => 'Misdirected Request', 'fr' => 'Requête mal dirigée', 'es' => 'Solicitud mal dirigida', 'ar' => 'طلب موجَّه بشكل خاطئ'], ['en' => 'The request was directed at a server that cannot produce a response.', 'fr' => 'La requête a été dirigée vers un serveur incapable de produire une réponse.', 'es' => 'La solicitud fue dirigida a un servidor que no puede producir una respuesta.', 'ar' => 'تم توجيه الطلب إلى خادم غير قادر على توليد استجابة.'], 'warning'),
            422 => self::row('heroicon-o-exclamation-triangle', ['en' => 'Unprocessable Entity', 'fr' => 'Entité non traitable', 'es' => 'Entidad no procesable', 'ar' => 'كيان غير قابل للمعالجة'], ['en' => 'The request is well formed but contains semantic errors.', 'fr' => 'La requête est bien formée mais contient des erreurs sémantiques.', 'es' => 'La solicitud está bien formada pero contiene errores semánticos.', 'ar' => 'الطلب مُنسَّق بشكل صحيح لكنه يحتوي على أخطاء دلالية.'], 'warning'),
            423 => self::row('heroicon-o-lock-closed', ['en' => 'Locked', 'fr' => 'Verrouillé', 'es' => 'Bloqueado', 'ar' => 'مقفل'], ['en' => 'The resource is locked and cannot be modified now.', 'fr' => 'La ressource est verrouillée et ne peut pas être modifiée maintenant.', 'es' => 'El recurso está bloqueado y no puede modificarse ahora.', 'ar' => 'المورد مقفل ولا يمكن تعديله حالياً.'], 'warning'),
            424 => self::row('heroicon-o-link', ['en' => 'Failed Dependency', 'fr' => 'Dépendance échouée', 'es' => 'Dependencia fallida', 'ar' => 'فشل التبعية'], ['en' => 'The request failed because a dependent request failed.', 'fr' => 'La requête a échoué car une requête dépendante a échoué.', 'es' => 'La solicitud falló porque falló una solicitud dependiente.', 'ar' => 'فشل الطلب بسبب فشل طلب تابع.'], 'warning'),
            425 => self::row('heroicon-o-clock', ['en' => 'Too Early', 'fr' => 'Trop tôt', 'es' => 'Demasiado pronto', 'ar' => 'مبكر جداً'], ['en' => 'The server is unwilling to risk processing a replayable request.', 'fr' => 'Le serveur ne souhaite pas risquer de traiter une requête rejouable.', 'es' => 'El servidor no quiere arriesgarse a procesar una solicitud reproducible.', 'ar' => 'الخادم غير مستعد لمعالجة طلب قد يُعاد تشغيله.'], 'warning'),
            426 => self::row('heroicon-o-arrow-up-circle', ['en' => 'Upgrade Required', 'fr' => 'Mise à niveau requise', 'es' => 'Actualización requerida', 'ar' => 'الترقية مطلوبة'], ['en' => 'The client should switch to another protocol.', 'fr' => 'Le client doit passer à un autre protocole.', 'es' => 'El cliente debe cambiar a otro protocolo.', 'ar' => 'يجب على العميل التبديل إلى بروتوكول آخر.'], 'warning'),
            428 => self::row('heroicon-o-clipboard-document-check', ['en' => 'Precondition Required', 'fr' => 'Précondition requise', 'es' => 'Condición previa requerida', 'ar' => 'الشرط المسبق مطلوب'], ['en' => 'The server requires the request to be conditional.', 'fr' => 'Le serveur exige que la requête soit conditionnelle.', 'es' => 'El servidor requiere que la solicitud sea condicional.', 'ar' => 'يشترط الخادم أن يكون الطلب مشروطاً.'], 'warning'),
            429 => self::row('heroicon-o-fire', ['en' => 'Too Many Requests', 'fr' => 'Trop de requêtes', 'es' => 'Demasiadas solicitudes', 'ar' => 'طلبات كثيرة جداً'], ['en' => 'You have sent too many requests in a short period.', 'fr' => 'Vous avez envoyé trop de requêtes en peu de temps.', 'es' => 'Has enviado demasiadas solicitudes en poco tiempo.', 'ar' => 'لقد أرسلت طلبات كثيرة جداً في فترة قصيرة.'], 'warning'),
            431 => self::row('heroicon-o-bars-3', ['en' => 'Request Header Fields Too Large', 'fr' => 'Champs d\'en-tête trop grands', 'es' => 'Campos de cabecera demasiado grandes', 'ar' => 'حقول رأس الطلب كبيرة جداً'], ['en' => 'The request header fields are too large.', 'fr' => 'Les champs d\'en-tête de la requête sont trop volumineux.', 'es' => 'Los campos de cabecera de la solicitud son demasiado grandes.', 'ar' => 'حقول رأس الطلب كبيرة جداً.'], 'warning'),
            451 => self::row('heroicon-o-scale', ['en' => 'Unavailable For Legal Reasons', 'fr' => 'Indisponible pour des raisons légales', 'es' => 'No disponible por razones legales', 'ar' => 'غير متاح لأسباب قانونية'], ['en' => 'Access to this resource has been denied for legal reasons.', 'fr' => 'L\'accès à cette ressource a été refusé pour des raisons légales.', 'es' => 'El acceso a este recurso ha sido denegado por razones legales.', 'ar' => 'تم رفض الوصول إلى هذا المورد لأسباب قانونية.'], 'danger'),
            500 => self::row('heroicon-o-server', ['en' => 'Internal Server Error', 'fr' => 'Erreur interne du serveur', 'es' => 'Error interno del servidor', 'ar' => 'خطأ داخلي في الخادم'], ['en' => 'Something went wrong on our end. Please try again later.', 'fr' => 'Quelque chose s\'est mal passé de notre côté. Veuillez réessayer plus tard.', 'es' => 'Algo salió mal de nuestro lado. Inténtalo de nuevo más tarde.', 'ar' => 'حدث خطأ ما من جانبنا. يرجى المحاولة لاحقاً.'], 'danger'),
            501 => self::row('heroicon-o-code-bracket', ['en' => 'Not Implemented', 'fr' => 'Non implémenté', 'es' => 'No implementado', 'ar' => 'غير مُنفَّذ'], ['en' => 'The server does not support the functionality required for this request.', 'fr' => 'Le serveur ne prend pas en charge la fonctionnalité nécessaire pour cette requête.', 'es' => 'El servidor no soporta la funcionalidad necesaria para esta solicitud.', 'ar' => 'لا يدعم الخادم الوظيفة المطلوبة لهذا الطلب.'], 'danger'),
            502 => self::row('heroicon-o-server', ['en' => 'Bad Gateway', 'fr' => 'Mauvaise passerelle', 'es' => 'Puerta de enlace incorrecta', 'ar' => 'بوابة غير صالحة'], ['en' => 'The server received an invalid response from an upstream server.', 'fr' => 'Le serveur a reçu une réponse invalide d\'un serveur en amont.', 'es' => 'El servidor recibió una respuesta no válida de un servidor upstream.', 'ar' => 'تلقّى الخادم استجابة غير صالحة من خادم المنبع.'], 'danger'),
            503 => self::row('heroicon-o-server', ['en' => 'Service Unavailable', 'fr' => 'Service indisponible', 'es' => 'Servicio no disponible', 'ar' => 'الخدمة غير متاحة'], ['en' => 'The server is temporarily unable to handle your request.', 'fr' => 'Le serveur est temporairement incapable de traiter votre requête.', 'es' => 'El servidor no puede manejar tu solicitud temporalmente.', 'ar' => 'الخادم غير قادر مؤقتاً على معالجة طلبك.'], 'danger'),
            504 => self::row('heroicon-o-clock', ['en' => 'Gateway Timeout', 'fr' => 'Délai de passerelle dépassé', 'es' => 'Tiempo de espera de la puerta de enlace agotado', 'ar' => 'انتهت مهلة البوابة'], ['en' => 'The server did not receive a timely response from an upstream server.', 'fr' => 'Le serveur n\'a pas reçu de réponse dans les délais d\'un serveur en amont.', 'es' => 'El servidor no recibió una respuesta oportuna de un servidor upstream.', 'ar' => 'لم يتلقَّ الخادم استجابة في الوقت المناسب من خادم المنبع.'], 'danger'),
            505 => self::row('heroicon-o-code-bracket', ['en' => 'HTTP Version Not Supported', 'fr' => 'Version HTTP non prise en charge', 'es' => 'Versión HTTP no compatible', 'ar' => 'إصدار HTTP غير مدعوم'], ['en' => 'The server does not support the HTTP protocol version used.', 'fr' => 'Le serveur ne prend pas en charge la version du protocole HTTP utilisée.', 'es' => 'El servidor no soporta la versión del protocolo HTTP utilizada.', 'ar' => 'لا يدعم الخادم إصدار بروتوكول HTTP المستخدم.'], 'danger'),
            506 => self::row('heroicon-o-arrows-pointing-out', ['en' => 'Variant Also Negotiates', 'fr' => 'La variante négocie aussi', 'es' => 'La variante también negocia', 'ar' => 'البديل يتفاوض أيضاً'], ['en' => 'The server has an internal configuration error.', 'fr' => 'Le serveur présente une erreur de configuration interne.', 'es' => 'El servidor tiene un error de configuración interno.', 'ar' => 'يواجه الخادم خطأً في الإعداد الداخلي.'], 'danger'),
            507 => self::row('heroicon-o-circle-stack', ['en' => 'Insufficient Storage', 'fr' => 'Espace de stockage insuffisant', 'es' => 'Almacenamiento insuficiente', 'ar' => 'مساحة التخزين غير كافية'], ['en' => 'The server cannot store the representation needed to complete the request.', 'fr' => 'Le serveur ne peut pas stocker la représentation nécessaire pour compléter la requête.', 'es' => 'El servidor no puede almacenar la representación necesaria para completar la solicitud.', 'ar' => 'لا يستطيع الخادم تخزين التمثيل اللازم لإتمام الطلب.'], 'danger'),
            508 => self::row('heroicon-o-arrow-path', ['en' => 'Loop Detected', 'fr' => 'Boucle détectée', 'es' => 'Bucle detectado', 'ar' => 'تم اكتشاف حلقة لا نهائية'], ['en' => 'The server detected a loop while processing the request.', 'fr' => 'Le serveur a détecté une boucle lors du traitement de la requête.', 'es' => 'El servidor detectó un bucle al procesar la solicitud.', 'ar' => 'اكتشف الخادم حلقة أثناء معالجة الطلب.'], 'danger'),
            510 => self::row('heroicon-o-puzzle-piece', ['en' => 'Not Extended', 'fr' => 'Non étendu', 'es' => 'No extendido', 'ar' => 'غير موسَّع'], ['en' => 'Further extensions to the request are required.', 'fr' => 'Des extensions supplémentaires à la requête sont nécessaires.', 'es' => 'Se requieren extensiones adicionales a la solicitud.', 'ar' => 'يحتاج الطلب إلى توسعات إضافية.'], 'danger'),
            511 => self::row('heroicon-o-wifi', ['en' => 'Network Authentication Required', 'fr' => 'Authentification réseau requise', 'es' => 'Autenticación de red requerida', 'ar' => 'مصادقة الشبكة مطلوبة'], ['en' => 'You need to authenticate with the network before accessing this resource.', 'fr' => 'Vous devez vous authentifier auprès du réseau avant d\'accéder à cette ressource.', 'es' => 'Debes autenticarte con la red antes de acceder a este recurso.', 'ar' => 'يجب عليك المصادقة مع الشبكة قبل الوصول إلى هذا المورد.'], 'warning'),
        ];
    }

    /**
     * @param array<string, string> $title
     * @param array<string, string> $description
     * @return array{icon: string, title: array<string, string>, description: array<string, string>, accent: string}
     */
    private static function row(string $icon, array $title, array $description, string $accent): array
    {
        return [
            'icon' => $icon,
            'title' => $title,
            'description' => $description,
            'accent' => $accent,
        ];
    }

    /**
     * @return array{icon: string, title: array<string, string>, description: array<string, string>, accent: string}
     */
    private static function default_error(): array
    {
        return self::row(
            'heroicon-o-exclamation-circle',
            ['en' => 'Something Went Wrong', 'fr' => 'Quelque chose s\'est mal passé', 'es' => 'Algo salió mal', 'ar' => 'حدث خطأ ما'],
            ['en' => 'An unexpected error occurred. Please refresh the page or try again later.', 'fr' => 'Une erreur inattendue s\'est produite. Veuillez actualiser la page ou réessayer plus tard.', 'es' => 'Ocurrió un error inesperado. Actualiza la página o inténtalo más tarde.', 'ar' => 'حدث خطأ غير متوقع. يرجى تحديث الصفحة أو المحاولة لاحقاً.'],
            'gray',
        );
    }
}
