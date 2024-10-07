export default interface Interface_Notice{
    id: number,
    name: string,
    imMessage: string,
    sendObjectIM: boolean,
    useBot: boolean,
    sendNotecard: boolean,
    notecardDetail: string,
    hoursRemaining: number,
    noticeNotecardLink: number
}
export const Default_Notice: Interface_Notice = {
    id: 0,
    name: "",
    imMessage: "",
    sendObjectIM: true,
    useBot: true,
    sendNotecard: true,
    notecardDetail: "",
    hoursRemaining: 0,
    noticeNotecardLink: 0
}