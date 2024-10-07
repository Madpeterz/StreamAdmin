export default interface Interface_Notecard{
    id: number,
    rentalLink: number,
    asNotice: boolean,
    noticeLink: number
}
export const Default_Notecard: Interface_Notecard = {
    id: 0,
    rentalLink: 0,
    asNotice: true,
    noticeLink: 0
}