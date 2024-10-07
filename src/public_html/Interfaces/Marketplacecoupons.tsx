export default interface Interface_Marketplacecoupons{
    id: number,
    cost: number,
    listingid: number,
    credit: number,
    claims: number,
    lastClaim: number
}
export const Default_Marketplacecoupons: Interface_Marketplacecoupons = {
    id: 0,
    cost: 0,
    listingid: 0,
    credit: 0,
    claims: 0,
    lastClaim: 0
}