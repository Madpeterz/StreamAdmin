export default interface Interface_Stream{
    id: number,
    serverLink: number,
    rentalLink: number,
    packageLink: number,
    port: number,
    needWork: boolean,
    adminUsername: string,
    adminPassword: string,
    djPassword: string,
    streamUid: string,
    mountpoint: string
}
export const Default_Stream: Interface_Stream = {
    id: 0,
    serverLink: 0,
    rentalLink: 0,
    packageLink: 0,
    port: 0,
    needWork: true,
    adminUsername: "",
    adminPassword: "",
    djPassword: "",
    streamUid: "",
    mountpoint: ""
}