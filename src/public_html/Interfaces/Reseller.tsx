export default interface Interface_Reseller{
    id: number,
    avatarLink: number,
    allowed: boolean,
    rate: number
}
export const Default_Reseller: Interface_Reseller = {
    id: 0,
    avatarLink: 0,
    allowed: true,
    rate: 0
}