
CREATE VIEW [dbo].[MerchantView]
AS
SELECT        dbo.Merchant.DescriptionShort, dbo.Merchant.DescriptionLong, dbo.Merchant.BasicTerms, dbo.Merchant.ExtentedTerms, dbo.Merchant.MerchantId, dbo.Merchant.IsLatest, dbo.Merchant.NetworkId, 
                         dbo.Merchant.MerchantName, dbo.Merchant.IsFeatured, dbo.Merchant.IsPopular, dbo.Merchant.IsHomePageFeatured, dbo.Merchant.HyphenatedString, 
                         '//cdn.cashrewards.com.au/' + dbo.Merchant.HyphenatedString + '.jpg' AS RegularImageUrl, '//cdn.cashrewards.com.au/' + 's/' + dbo.Merchant.HyphenatedString + '.jpg' AS SmallImageUrl, 
                         '//cdn.cashrewards.com.au/' + 'm/' + dbo.Merchant.HyphenatedString + '.jpg' AS MediumImageUrl, '//cdn.cashrewards.com.au/' + dbo.Merchant.HyphenatedString + '.jpg' AS RegularImageUrlSecure, 
                         '//cdn.cashrewards.com.au/' + 's/' + dbo.Merchant.HyphenatedString + '.jpg' AS SmallImageUrlSecure, '//cdn.cashrewards.com.au/' + 'm/' + dbo.Merchant.HyphenatedString + '.jpg' AS MediumImageUrlSecure, 
                         NEWID() AS RandomNumber, dbo.MerchantClientMap.ClientId, dbo.ConsolidatedMerchantTierView.TierCommTypeId, dbo.ConsolidatedMerchantTierView.Commission, 
                         dbo.ConsolidatedMerchantTierView.ClientComm, dbo.ConsolidatedMerchantTierView.MemberComm, dbo.ConsolidatedMerchantTierView.TierTypeId, dbo.ConsolidatedMerchantTierView.TierCssClass, 
                         dbo.ConsolidatedMerchantTierView.TrackingLink, dbo.MerchantTierType.IsExtra, '//cdn.cashrewards.com.au/' + 'flags/' + 'AU' + '.jpg' AS FlagImageUrl, dbo.MerchantOfferCountView.OfferCount, 
                         dbo.Client.RewardName, dbo.Client.ClientProgramTypeId, dbo.ConsolidatedMerchantTierView.TierDescription, dbo.ConsolidatedMerchantTierView.TierName, dbo.Merchant.WebsiteUrl, 
                         dbo.Merchant.TrackingTime, dbo.Merchant.ApprovalTime, ISNULL(dbo.Client.EarningsExchange, 0.0) AS Rate, dbo.Merchant.NotificationMsg, dbo.Merchant.ConfirmationMsg, dbo.Merchant.MobileEnabled, 
                         dbo.ConsolidatedMerchantTierView.TierCount, dbo.Merchant.IsToolbarEnabled, dbo.Merchant.IsLuxuryBrand
FROM            dbo.Merchant INNER JOIN
                         dbo.MerchantClientMap ON dbo.Merchant.MerchantId = dbo.MerchantClientMap.MerchantId INNER JOIN
                         dbo.ConsolidatedMerchantTierView ON dbo.Merchant.MerchantId = dbo.ConsolidatedMerchantTierView.MerchantId AND dbo.MerchantClientMap.ClientId = dbo.ConsolidatedMerchantTierView.ClientId INNER JOIN
                         dbo.MerchantTierType ON dbo.ConsolidatedMerchantTierView.TierTypeId = dbo.MerchantTierType.TierTypeId INNER JOIN
                         dbo.Client ON dbo.MerchantClientMap.ClientId = dbo.Client.ClientId INNER JOIN
                         dbo.MerchantOfferCountView ON dbo.Merchant.MerchantId = dbo.MerchantOfferCountView.MerchantId
WHERE        (dbo.Merchant.Status = 1) AND (dbo.Client.Status = 1)


GO


