export default interface Interface_Treevender{
    id: number,
    name: string,
    textureWaiting: string,
    textureInuse: string,
    hideSoldout: boolean
}
export const Default_Treevender: Interface_Treevender = {
    id: 0,
    name: "",
    textureWaiting: "",
    textureInuse: "",
    hideSoldout: true
}