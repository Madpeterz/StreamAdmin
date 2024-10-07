export default interface Interface_Server{
    id: number,
    domain: string,
    controlPanelURL: string,
    ipaddress: string,
    bandwidth: number,
    bandwidthType: string,
    totalStorage: number,
    totalStorageType: string
}
export const Default_Server: Interface_Server = {
    id: 0,
    domain: "",
    controlPanelURL: "",
    ipaddress: "",
    bandwidth: 0,
    bandwidthType: "",
    totalStorage: 0,
    totalStorageType: ""
}