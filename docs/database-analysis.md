# Análisis de Estructura de Base de Datos
Descripción General
La base de datos gestiona información de crédito para suscriptores a través de diferentes períodos de reporte. Cada suscriptor puede tener múltiples reportes, y cada reporte contiene varios tipos de información de deuda.
Tablas
1. subscriptions
Tabla principal que almacena información del suscriptor.
2. subscription_reports
Reportes de crédito asociados a suscriptores para períodos específicos.
3. report_loans
Información de préstamos dentro de los reportes.
4. report_other_debts
Otras deudas no clasificadas como préstamos o tarjetas de crédito.
5. report_credit_cards
Información de tarjetas de crédito.
Diagrama de Relaciones
subscriptions (1) ──────< (N) subscription_reports
                                    │
                                    ├──< (N) report_loans
                                    ├──< (N) report_other_debts
                                    └──< (N) report_credit_cards
# Mapeo de Exportación para el excel 
Columna de ExportaciónOrigenIDENTIFICACIÓNsubscription_reports.idNombre Completosubscriptions.full_nameDNIsubscriptions.documentCorreo electrónicosubscriptions.emailTeléfonosubscriptions.phoneCompañíaloans.bank / credit_cards.bank / other_debts.entityTipo de deuda"Préstamo" / "Tarjeta de crédito" / "Otra deuda"Situaciónloans.status (solo para préstamos)Atrasoloans/other_debts.expiration_daysEntidadIgual que CompañíaMonto totalloans/other_debts.amountLínea totalcredit_cards.lineLínea de tiempocredit_cards.usedReporte subido elsubscription_reports.created_atEstadosubscription_reports status (derivado)
Consideraciones de Optimización de Consultas

# Carga Anticipada Requerida:

Cargar subscriptions con subscription_reports
Cargar subscription_reports con loans, other_debts, credit_cards


# Índices Recomendados:

subscription_reports.created_at (para filtrado por fechas)
Las claves foráneas ya están indexadas por defecto


# Volumen de Datos Esperado:

Potencial de millones de registros de deuda en todos los tipos de reportes
Cada suscriptor puede tener múltiples reportes (períodos mensuales)
Cada reporte puede tener múltiples deudas de cada tipo
Debe manejar exportaciones con más de 100K filas eficientemente


# Estrategia de Optimización de Memoria:

Usar procesamiento por chunks (1000-5000 registros por lote)
Transmitir datos a Excel en streaming en lugar de cargar todo en memoria
Usar generadores para iteración de datos
Considerar queue jobs para exportaciones grandes