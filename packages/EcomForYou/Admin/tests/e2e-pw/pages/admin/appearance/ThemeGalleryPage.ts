import path from "path";
import { expect, type Page } from "@playwright/test";
import { BasePage } from "../../BasePage";
import { DATA_PATH } from "../../../utils/paths";

export class ThemeGalleryPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    private group(heading: string) {
        return this.page
            .getByText(heading, { exact: true })
            .locator("../..");
    }

    private card(name: string) {
        return this.page
            .locator("div.box-shadow")
            .filter({ has: this.page.getByText(name, { exact: true }) });
    }

    async open(): Promise<void> {
        await this.visit("admin/appearance/themes");

        await expect(this.page.getByText("My Themes", { exact: true })).toBeVisible();
    }

    async expectOnlyInstalledThemes(): Promise<void> {
        await this.open();

        const installed = this.group("My Themes");

        await expect(installed).toBeVisible();

        await expect(
            installed.getByRole("link", { name: "Customize" }).first(),
        ).toBeVisible();

        await expect(
            this.page.getByText("Buy Themes", { exact: true }),
        ).toHaveCount(0);

        await expect(
            this.page.getByRole("link", { name: "View & Buy" }),
        ).toHaveCount(0);
    }

    async uploadTheme(fileName: string, name: string): Promise<void> {
        await this.open();

        await this.page.getByRole("button", { name: "Upload Theme" }).click();

        await this.page
            .locator('input[name="archive"]')
            .setInputFiles(path.join(DATA_PATH, "themes", fileName));

        await this.page.getByRole("button", { name: "Upload", exact: true }).click();

        await expect(
            this.page.getByText(`${name} was uploaded`),
        ).toBeVisible({ timeout: 60 * 1000 });

        await expect(this.card(name)).toBeVisible();
    }

    async expectPreviewShows(name: string, text: string): Promise<void> {
        await this.open();

        const [preview] = await Promise.all([
            this.page.context().waitForEvent("page"),
            this.card(name).getByRole("button", { name: "Preview" }).click(),
        ]);

        await preview.waitForLoadState();

        await expect(preview.getByText(text)).toBeVisible();

        await preview.getByRole("button", { name: "Exit Preview" }).click();

        await expect(preview.getByText(text)).toHaveCount(0);

        await preview.close();
    }

    async activateTheme(name: string): Promise<void> {
        await this.open();

        await this.card(name).getByRole("button", { name: "Activate" }).click();

        await this.page
            .locator("form")
            .filter({ hasText: "Activate Theme" })
            .getByRole("button", { name: "Activate" })
            .click();

        await expect(this.page.getByText(`${name} is now active on`)).toBeVisible();
    }

    async expectStorefrontShows(text: string): Promise<void> {
        const storefront = await this.page.context().newPage();

        await storefront.goto("");

        await expect(storefront.getByText(text)).toBeVisible();

        await storefront.close();
    }

    async deleteTheme(name: string): Promise<void> {
        await this.open();

        await this.card(name).getByRole("button", { name: "Delete" }).click();

        await this.page.getByRole("button", { name: "Agree" }).click();

        await expect(this.page.getByText(`${name} was deleted.`)).toBeVisible();

        await expect(this.card(name)).toHaveCount(0);
    }
}
