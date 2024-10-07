export default interface Interface_Textureconfig{
    id: number,
    name: string,
    offline: string,
    waitOwner: string,
    stockLevels: string,
    makePayment: string,
    inUse: string,
    renewHere: string,
    proxyRenew: string,
    gettingDetails: string,
    requestDetails: string
}
export const Default_Textureconfig: Interface_Textureconfig = {
    id: 0,
    name: "",
    offline: "",
    waitOwner: "",
    stockLevels: "",
    makePayment: "",
    inUse: "",
    renewHere: "",
    proxyRenew: "",
    gettingDetails: "",
    requestDetails: ""
}