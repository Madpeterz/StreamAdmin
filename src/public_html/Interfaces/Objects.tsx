export default interface Interface_Objects{
    id: number,
    avatarLink: number,
    regionLink: number,
    objectUUID: string,
    objectName: string,
    objectMode: string,
    objectXYZ: string,
    lastSeen: number
}
export const Default_Objects: Interface_Objects = {
    id: 0,
    avatarLink: 0,
    regionLink: 0,
    objectUUID: "",
    objectName: "",
    objectMode: "",
    objectXYZ: "",
    lastSeen: 0
}