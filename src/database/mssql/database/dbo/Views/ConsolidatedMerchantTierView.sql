CREATE VIEW [dbo].[ConsolidatedMerchantTierView]        
AS

WITH cte AS (
 SELECT MerchantId, 
        ClientId, 
        MerchantTierId, 
        Commission,
        ClientComm, 
        MemberComm, 
        Rate, 
        TierCommTypeId,
        ROW_NUMBER() OVER (PARTITION BY MerchantId, ClientId,TierCommTypeId ORDER BY (Commission * ClientComm * MemberComm) DESC) as RowNumber,
        ROW_NUMBER() OVER (PARTITION BY MerchantId, ClientId ORDER BY MerchantId) as RowNumber1,
		Rank() OVER (PARTITION BY MerchantId, ClientId ORDER BY TierCommTypeId DESC) as RankNumber
    FROM MerchantTierView 
)
SELECT T1.ClientId,
    T1.MerchantId,
    T1.MerchantTierId,
    T1.TotalCount AS TierCount,
    TrackingLink,
    ClientTierId,
    ScheduleRateId,
    TierTypeId,
    T1.TierCommTypeId,
    T1.Commission,
    T1.ClientComm,
    T1.MemberComm,
    StartDate,
    EndDate,
    TierCssClass,
    TierName,
    TierDescription,
    Identifier,
    IsExtra,
    T1.Rate	
FROM MerchantTierView INNER JOIN (
    SELECT MerchantId, 
        ClientId, 
        MerchantTierId, 
        Commission,
        ClientComm, 
        MemberComm, 
        Rate, 
        TierCommTypeId,
         (
            SELECT CAST(MAX(RowNumber1) AS INT) 
            FROM cte AS T2 
            WHERE T2.ClientId = cte.ClientId AND T2.MerchantId = cte.MerchantId 
            GROUP BY ClientId, MerchantId
        ) as TotalCount
    FROM cte 
    WHERE cte.RowNumber = 1 AND cte.RankNumber = 1
)  AS T1
ON (MerchantTierView.MerchantTierId = T1.MerchantTierId AND  MerchantTierView.MerchantId = T1.MerchantId AND MerchantTierView.ClientId = T1.ClientId)
