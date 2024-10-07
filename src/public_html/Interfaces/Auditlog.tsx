export default interface Interface_Auditlog{
    id: number,
    store: string,
    sourceid: string,
    valuename: string,
    oldvalue: string,
    newvalue: string,
    unixtime: number,
    avatarLink: number
}
export const Default_Auditlog: Interface_Auditlog = {
    id: 0,
    store: "",
    sourceid: "",
    valuename: "",
    oldvalue: "",
    newvalue: "",
    unixtime: 0,
    avatarLink: 0
}