export default interface Interface_Botconfig{
    id: number,
    avatarLink: number,
    secret: string,
    notecards: boolean,
    ims: boolean,
    invites: boolean,
    inviteGroupUUID: string,
    httpMode: boolean,
    httpURL: string
}
export const Default_Botconfig: Interface_Botconfig = {
    id: 0,
    avatarLink: 0,
    secret: "",
    notecards: true,
    ims: true,
    invites: true,
    inviteGroupUUID: "",
    httpMode: true,
    httpURL: ""
}